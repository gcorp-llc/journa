<?php

namespace App\Jobs;

use App\Traits\InteractsWithHttp;
use App\Services\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class CrawlNewsContentJob implements ShouldQueue
{
    use Queueable, InteractsWithHttp;

    private const RETRY_DELAY = 60;
    private const MAX_RETRIES = 3;

    private string $siteName;
    private int $siteId;
    private int $parentCategoryId; // تغییر نام برای وضوح بیشتر
    private string $url;
    private array $config;
    private string $jobId;

    public $tries = self::MAX_RETRIES;

    public function __construct(string $siteName, int $siteId, int $categoryId, string $url, array $newsSelectors = [])
    {
        $this->siteName = $siteName;
        $this->siteId = $siteId;
        $this->parentCategoryId = $categoryId;
        $this->url = $url;
        $this->config = ['news_selectors' => $newsSelectors];
        $this->jobId = uniqid('content_', true);
    }

    public function handle(TranslationService $translationService)
    {
        try {
            if (empty($this->config['news_selectors'])) {
                $this->loadConfig();
            }

            $response = $this->sendRequest($this->url, 'get', ['job_id' => $this->jobId]);
            $html = $response->body();

            if (strlen($html) < 500) throw new \Exception("HTML ناقص یا خالی");

            $crawler = new Crawler($html);
            $jsonLd = $this->extractJsonLdData($crawler);

            // 1. استخراج عنوان
            $title = $jsonLd['headline'] ?? $this->extractBySelector($crawler, 'title');
            if (!$title) {
                $title = $crawler->filter('title')->count() ? $crawler->filter('title')->text() : null;
            }

            if (empty($title)) throw new \Exception("عنوان یافت نشد");

            // 2. استخراج محتوا
            $content = $this->extractContent($crawler);
            if (empty($content) && !empty($jsonLd['description'])) {
                $content = "<p>" . $jsonLd['description'] . "</p>";
            }

            if (empty($content) || strlen(strip_tags($content)) < 50) {
                throw new \Exception("محتوا کوتاه است");
            }

            // 3. ترجمه و ذخیره خبر
            $translations = $translationService->translateArray(
                ['title' => $title, 'content' => $content],
                ['title', 'content']
            );

            // 4. ذخیره خبر در دیتابیس
            $newsId = $this->saveNews($translations, $jsonLd['image'] ?? null);

            // 5. مدیریت پیشرفته دسته‌بندی‌ها (بخش اصلاح شده)
            $this->processCategories($newsId, $crawler);

            // 6. پردازش تصویر
            $this->dispatchImageJob($newsId, $html, $jsonLd['image'] ?? null, $translations['title']['en'] ?? 'news');

            Log::info("✅ [خبر ذخیره شد]", ['id' => $newsId, 'title' => Str::limit($title, 30)]);

        } catch (\Exception $e) {
            Log::error("❌ [خطای محتوا]", ['url' => $this->url, 'msg' => $e->getMessage()]);
            $this->release(self::RETRY_DELAY);
        }
    }

    /**
     * بخش جدید برای مدیریت دقیق‌تر دسته‌بندی‌ها
     */
    private function processCategories(int $newsId, Crawler $crawler): void
    {
        $categoryIds = [$this->parentCategoryId]; // همیشه دسته‌بندی مادر را نگه دار

        // تلاش برای پیدا کردن دسته‌بندی از داخل صفحه (مثلاً Breadcrumb)
        // فرض بر این است که در کانفیگ سلکتوری به نام 'category' یا 'breadcrumb' دارید
        $categorySelector = $this->config['news_selectors']['category'] ?? $this->config['news_selectors']['breadcrumb'] ?? null;

        if ($categorySelector) {
            try {
                $detectedCategoryName = $crawler->filter($categorySelector)->count() > 0
                    ? trim($crawler->filter($categorySelector)->last()->text())
                    : null;

                if ($detectedCategoryName) {
                    // جستجو در دیتابیس برای پیدا کردن ID این دسته‌بندی
                    $detectedId = DB::table('news_site_categories')
                        ->where('news_site_id', $this->siteId)
                        ->where(function($q) use ($detectedCategoryName) {
                            $q->where('title', 'LIKE', "%{$detectedCategoryName}%") // نام فارسی یا اصلی
                            ->orWhere('url', 'LIKE', "%" . Str::slug($detectedCategoryName) . "%");
                        })
                        ->value('id'); // فرض بر این است که ستون id داریم (نه category_id)

                    if ($detectedId) {
                        $categoryIds[] = $detectedId;
                        Log::info("🏷️ [دسته‌بندی هوشمند یافت شد]", ['name' => $detectedCategoryName, 'id' => $detectedId]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("⚠️ خطا در استخراج دسته‌بندی هوشمند: " . $e->getMessage());
            }
        }

        // حذف تکراری‌ها و ذخیره
        $categoryIds = array_unique($categoryIds);

        foreach ($categoryIds as $catId) {
            DB::table('category_news')->insertOrIgnore([
                'news_id' => $newsId,
                'category_id' => $catId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function extractContent(Crawler $crawler): string
    {
        $selectors = $this->config['news_selectors'];

        // حذف عناصر مزاحم
        $unwanted = array_merge(
            ['script', 'style', 'iframe', 'nav', 'footer', '.ad', '.social-share'],
            $selectors['unwanted_content_selectors'] ?? []
        );

        foreach ($unwanted as $sel) {
            $crawler->filter($sel)->each(fn(Crawler $node) =>
            $node->getNode(0)->parentNode->removeChild($node->getNode(0))
            );
        }

        $html = '';
        $crawler->filter($selectors['content'])->each(function (Crawler $node) use (&$html) {
            $html .= $this->cleanHtml($node->outerHtml());
        });

        return $html;
    }

    private function cleanHtml(string $html): string
    {
        // حذف تمام اتریبیوت‌ها به جز src و href برای تمیزکاری
        $html = preg_replace('/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i', '<$1$2>', $html);
        // حذف تگ‌های خالی
        return strip_tags($html, '<p><h2><h3><h4><ul><li><b><strong><br>');
    }

    private function saveNews(array $translations, ?string $coverImage): int
    {
        return DB::transaction(function () use ($translations, $coverImage) {
            $slug = Str::slug(Str::limit($translations['title']['en'] ?? uniqid(), 50));
            // اطمینان از یکتایی اسلاگ
            if (DB::table('news')->where('slug', $slug)->exists()) {
                $slug .= '-' . time();
            }

            DB::table('news')->updateOrInsert(
                ['source_url' => $this->url],
                [
                    'title' => json_encode($translations['title'], JSON_UNESCAPED_UNICODE),
                    'content' => json_encode($translations['content'], JSON_UNESCAPED_UNICODE),
                    'slug' => $slug,
                    'published_at' => now(),
                    'news_site_id' => $this->siteId,
                    'status' => 'published',
                    'updated_at' => now()
                ]
            );

            return DB::table('news')->where('source_url', $this->url)->value('id');
        });
    }

    // توابع کمکی دیگر مثل extractJsonLdData و loadConfig مشابه قبل هستند...
    // برای خلاصه شدن کد تکرار نشدند اما باید وجود داشته باشند.

    private function extractJsonLdData(Crawler $crawler): array { return []; /* پیاده‌سازی قبلی */ }
    private function extractBySelector(Crawler $crawler, string $key): ?string
    {
        if (empty($this->config['news_selectors'][$key])) return null;
        try {
            return trim($crawler->filter($this->config['news_selectors'][$key])->text());
        } catch (\Exception $e) { return null; }
    }
    private function loadConfig(): void { /* ... */ }

    private function dispatchImageJob($newsId, $html, $image, $slug) {
        $imgConfig = $this->config;
        if ($image) $imgConfig['news_selectors']['json_ld_image'] = $image;

        ProcessNewsImageJob::dispatch($newsId, $this->siteName, $this->url, $imgConfig, $html, $slug)
            ->delay(now()->addSeconds(2));
    }
}
