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
    private const MIN_PARAGRAPH_LENGTH = 30;
    private const MAX_RETRIES = 3;

    private string $siteName;
    private int $siteId;
    private int $categoryId;
    private string $url;
    private ?string $title = null;
    private ?array $config = null;
    private string $jobId;
    private float $startTime;

    public $tries = self::MAX_RETRIES;

    public function __construct(string $siteName, int $siteId, int $categoryId, string $url, array $newsSelectors = [])
    {
        $this->siteName = $siteName;
        $this->siteId = $siteId;
        $this->categoryId = $categoryId;
        $this->url = $url;
        $this->config = ['news_selectors' => $newsSelectors];
        $this->jobId = uniqid('crawl_content_', true);
        $this->startTime = microtime(true);
    }

    public function handle(TranslationService $translationService)
    {
        try {
            Log::info("🚀 [شروع پردازش محتوا]", [
                'job_id' => $this->jobId,
                'site' => $this->siteName,
                'url' => $this->url
            ]);

            if (empty($this->config['news_selectors'])) {
                $this->loadConfig();
            }

            // 1. دریافت صفحه (با استفاده از متد Trait)
            $response = $this->sendRequest($this->url, 'get', ['job_id' => $this->jobId]);
            $html = $response->body();

            if (empty($html)) throw new \Exception("HTML دریافتی خالی است");

            $crawler = new Crawler($html);

            // 2. استخراج داده‌های ساختاریافته (JSON-LD)
            $jsonLdData = $this->extractJsonLdData($crawler);

            // تعیین عنوان (اولویت با JSON-LD)
            $this->title = $jsonLdData['headline'] ?? $this->extractTitleViaSelectors($crawler);

            // فال‌بک نهایی برای عنوان
            if (empty($this->title)) {
                $pageTitle = $crawler->filter('title')->count() > 0 ? $crawler->filter('title')->text() : '';
                $this->title = trim(str_replace(['| AP News', '- BBC', 'Breaking News'], '', $pageTitle));
            }

            if (empty($this->title)) {
                throw new \Exception("عنوان خبر پیدا نشد");
            }

            // 3. استخراج متن خبر
            $content = $this->extractContent($crawler, $this->config['news_selectors']);

            // اگر متن HTML پیدا نشد، از توضیحات JSON-LD استفاده کن
            if (empty($content) && !empty($jsonLdData['description'])) {
                $content = "<p>" . $jsonLdData['description'] . "</p>";
                Log::info("ℹ️ [استفاده از توضیحات JSON-LD به جای متن]", ['job_id' => $this->jobId]);
            }

            if (empty($content) || strlen(strip_tags($content)) < 50) {
                throw new \Exception("محتوای خبر بسیار کوتاه یا خالی است");
            }

            // 4. ترجمه و ذخیره
            $translations = $this->translateContent($content, $translationService);

            // استخراج تصویر (اولویت با JSON-LD)
            $coverImage = $jsonLdData['image'] ?? null;

            $newsId = $this->saveNews($translations);
            $this->saveCategory($newsId);

            // 5. آماده‌سازی کانفیگ برای جاب تصویر
            $imageConfig = $this->config;
            if ($coverImage) {
                // پاس دادن URL تصویر پیدا شده به جاب بعدی
                $imageConfig['news_selectors']['json_ld_image'] = $coverImage;
            }

            ProcessNewsImageJob::dispatch(
                $newsId,
                $this->siteName,
                $this->url,
                $imageConfig,
                $html, // پاس دادن HTML برای جلوگیری از درخواست مجدد
                $translations['title']['en'] ?? 'news'
            )->delay(now()->addSeconds(2));

            Log::info("✨ [پایان موفقیت‌آمیز]", ['job_id' => $this->jobId, 'news_id' => $newsId]);

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    private function extractJsonLdData(Crawler $crawler): array
    {
        $data = ['headline' => null, 'description' => null, 'image' => null];
        try {
            $crawler->filter('script[type="application/ld+json"]')->each(function (Crawler $node) use (&$data) {
                $json = json_decode($node->text(), true);
                if (!$json) return;
                $items = isset($json['@graph']) ? $json['@graph'] : [$json];

                foreach ($items as $item) {
                    $type = $item['@type'] ?? '';
                    if (in_array($type, ['NewsArticle', 'Article', 'ReportageNewsArticle', 'BlogPosting'])) {
                        $data['headline'] = $item['headline'] ?? $data['headline'];
                        $data['description'] = $item['description'] ?? $item['articleBody'] ?? $data['description'];

                        if (isset($item['image'])) {
                            $img = $item['image'];
                            // اصلاح برای گرفتن یک URL واحد از ساختارهای مختلف
                            if (is_string($img)) {
                                $data['image'] = $img;
                            } elseif (is_array($img)) {
                                $data['image'] = $img['url'] ?? ($img[0]['url'] ?? null);
                            }
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            // خطای پارس JSON نباید کل پروسه را متوقف کند
        }
        return $data;
    }

    private function extractTitleViaSelectors(Crawler $crawler): ?string
    {
        try {
            $selector = $this->config['news_selectors']['title'];
            if ($crawler->filter($selector)->count() > 0) {
                return trim($crawler->filter($selector)->first()->text());
            }
        } catch (\Exception $e) {}
        return null;
    }

    private function extractContent(Crawler $crawler, array $selectors): string
    {
        // 1. حذف عناصر مزاحم قبل از استخراج
        $unwanted = array_merge(
            ['script', 'style', 'iframe', 'nav', 'footer', 'aside', '.ad', '.banner', 'form', 'button'],
            $selectors['unwanted_content_selectors'] ?? []
        );

        foreach ($unwanted as $selector) {
            try {
                $crawler->filter($selector)->each(function (Crawler $node) {
                    $node->getNode(0)->parentNode->removeChild($node->getNode(0));
                });
            } catch (\Exception $e) {}
        }

        $contentHtml = '';
        // 2. استخراج پاراگراف‌ها و حذف لینک‌ها
        try {
            $crawler->filter($selectors['content'])->each(function (Crawler $parentNode) use (&$contentHtml) {
                if ($parentNode->nodeName() === 'p') {
                    $contentHtml .= $this->cleanHtmlNode($parentNode);
                } else {
                    $parentNode->filter('p, h2, h3, ul, blockquote')->each(function ($child) use (&$contentHtml) {
                        $contentHtml .= $this->cleanHtmlNode($child);
                    });
                }
            });
        } catch (\Exception $e) {}

        return $contentHtml;
    }

    /**
     * تمیز کردن نود HTML: حذف لینک‌ها و فیلتر محتوای کوتاه/تبلیغاتی
     */
    private function cleanHtmlNode(Crawler $node): string
    {
        $text = trim($node->text());
        if (strlen($text) < self::MIN_PARAGRAPH_LENGTH) return '';

        // فیلتر کلمات کلیدی تبلیغاتی/ناخواسته
        if (preg_match('/(read more|subscribe|copyright|click here|follow us|continue reading)/i', $text)) return '';

        $tag = $node->nodeName();
        $html = $node->html();

        // ✅ حذف تگ‌های <a> از داخل محتوا
        $html = preg_replace('/<a\s+[^>]*>(.*?)<\/a>/is', '$1', $html);

        return "<{$tag}>" . $html . "</{$tag}>\n";
    }

    private function loadConfig(): void
    {
        $config = config('crawler.sites.' . $this->siteName);
        if (!$config) throw new \Exception("کانفیگ یافت نشد: " . $this->siteName);
        $this->config = $config;
    }

    private function translateContent(string $content, TranslationService $service): array {
        return $service->translateArray(['title' => $this->title, 'content' => $content], ['title', 'content']);
    }

    private function saveNews(array $translations): int {
        return DB::transaction(function () use ($translations) {
            $titleEn = $translations['title']['en'] ?? uniqid();
            $slug = Str::slug(Str::limit($titleEn, 50));

            // اطمینان از یکتا بودن اسلاگ
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('news')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            DB::table('news')->updateOrInsert(
                ['source_url' => $this->url],
                [
                    'title' => json_encode($translations['title']),
                    'content' => json_encode($translations['content']),
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

    private function saveCategory(int $newsId): void {
        DB::table('category_news')->insertOrIgnore([
            'news_id' => $newsId, 'category_id' => $this->categoryId,
            'created_at' => now(), 'updated_at' => now()
        ]);
    }

    private function handleError(\Exception $e): void {
        Log::error("❌ [خطای کانتنت]", ['url' => $this->url, 'msg' => $e->getMessage()]);
        if ($this->attempts() < self::MAX_RETRIES) $this->release(self::RETRY_DELAY);
        else $this->fail($e);
    }
}
