<?php

namespace App\Jobs;

use App\Services\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class CrawlNewsContentJob implements ShouldQueue
{
    use Queueable;

    private const HTTP_TIMEOUT = 15;
    private const RETRY_DELAY = 60;
    private const MIN_PARAGRAPH_LENGTH = 30;
    private const MAX_RETRIES = 3;

    private string $siteName;
    private int $siteId;
    private int $categoryId;
    private string $url;
    private ?string $title = null;
    private ?array $config = null;
    private ?string $html = null;

    public $tries = self::MAX_RETRIES;

    public function __construct(string $siteName, int $siteId, int $categoryId, string $url, array $newsSelectors = [])
    {
        $this->siteName = $siteName;
        $this->siteId = $siteId;
        $this->categoryId = $categoryId;
        $this->url = $url;
        $this->config = ['news_selectors' => $newsSelectors];
    }

    public function handle(TranslationService $translationService)
    {
        try {
            Log::info("شروع پردازش خبر: {$this->url}");

            if (empty($this->config['news_selectors'])) {
                $this->config = $this->getConfig();
            }

            $this->html = $this->fetchPage();
            $content = $this->extractContent($this->html, $this->config['news_selectors']);
            $translations = $this->translateContent($content, $translationService);
            $newsId = $this->saveNews($translations);
            $this->saveCategory($newsId);

            Log::info("خبر با موفقیت ذخیره شد.", [
                'news_id' => $newsId,
                'url' => $this->url,
                'content_length' => strlen(strip_tags($content)),
                'paragraph_count' => substr_count($content, '<p>'),
            ]);

            ProcessNewsImageJob::dispatchSync($newsId, $this->siteName, $this->url, $this->config, $this->html)->delay(now()->addSeconds(3));

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    private function getConfig(): array
    {
        $config = config('crawler.sites.' . $this->siteName);
        if (empty($config['news_selectors']['content']) || empty($config['news_selectors']['title'])) {
            throw new \Exception("سلکتورهای محتوا یا عنوان برای سایت {$this->siteName} تعریف نشده‌اند.");
        }
        return $config;
    }

    private function fetchPage(): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive',
        ])->timeout(self::HTTP_TIMEOUT)->get($this->url);

        if ($response->status() == 403) {
            Log::warning("دسترسی به URL ممنوع است: {$this->url}, وضعیت: 403");
            $this->release(self::RETRY_DELAY);
        }

        if (!$response->ok()) {
            throw new \Exception("خطا در دریافت URL: {$this->url}, وضعیت: {$response->status()}");
        }

        $html = $response->body();
        if (empty($html)) {
            throw new \Exception("محتوای HTML خالی است برای URL: {$this->url}");
        }

        return $html;
    }

    private function extractContent(string $html, array $selectors): string
    {
        $crawler = new Crawler($html);

        // استخراج عنوان
        if ($crawler->filter($selectors['title'])->count()) {
            $this->title = trim($crawler->filter($selectors['title'])->text());
        }

        // حذف المنت‌های ناخواسته عمومی
        $this->removeUnwantedElements($crawler);

        // حذف المنت‌های تبلیغاتی خاص سایت
        if (!empty($selectors['unwanted_content_selectors'])) {
            $unwantedSelectors = implode(', ', array_map('trim', $selectors['unwanted_content_selectors']));
            $crawler->filter($selectors['content'] . ' ' . $unwantedSelectors)->each(function (Crawler $node) {
                $domNode = $node->getNode(0);
                if ($domNode && $domNode->parentNode) {
                    $domNode->parentNode->removeChild($domNode);
                }
            });
        }

        // استخراج تمام پاراگراف‌ها
        $contentHtml = '';
        if ($crawler->filter($selectors['content'])->count()) {
            $crawler->filter($selectors['content'] . ' p')->each(function (Crawler $node) use (&$contentHtml) {
                $text = trim($node->html());
                if (strlen(strip_tags($text)) >= self::MIN_PARAGRAPH_LENGTH &&
                    !preg_match('/(advertisement|sponsor|ads|subscribe|sign up)/i', strip_tags($text))) {
                    $contentHtml .= "<p>{$text}</p>\n";
                }
            });
        }

        if (empty($contentHtml)) {
            throw new \Exception("محتوای قابل استخراج برای {$this->url} یافت نشد.");
        }

        $cleanedContent = $this->sanitizeHtml($contentHtml);

        if (strlen(strip_tags($cleanedContent)) < 100) {
            throw new \Exception("محتوای استخراج‌شده بسیار کوتاه است: {$this->url}");
        }

        return $cleanedContent;
    }

    private function removeUnwantedElements(Crawler $crawler): void
    {
        $unwantedSelectors = [
            'script', 'style', 'iframe', 'nav', 'footer',
            '.ad', '.banner', '.advertisement',
            '[class*="ad-"]', '[id*="ad-"]',
            '.social-share', '.related-posts', '.comments',
            '.Component-video-0', '.Component-image-0', '.Component-caption-0',
            '.inline-content', '.promo-content', '.ad-block',
            'figure[class*="ad"]', 'div[class*="fs-feed-ad"]',
        ];

        $crawler->filter(implode(', ', $unwantedSelectors))->each(function (Crawler $node) {
            $domNode = $node->getNode(0);
            if ($domNode && $domNode->parentNode) {
                $domNode->parentNode->removeChild($domNode);
            }
        });
    }

    private function sanitizeHtml(string $html): string
    {
        $patterns = [
            '/<(?!p|br|strong|em|a\s*\/?)[^>]+>/i' => '', // فقط تگ‌های مجاز
            '/<script\b[^>]*>.*?<\/script>/is' => '',
            '/freestar\.queue\.push\s*\(.*?\);/is' => '',
            '/document\.querySelectorAll\s*\(.*?\);/is' => '',
            '/window\.fsadcount.*?;/is' => '',
            '/Math\.random\s*\(.*?\)/is' => '',
            '/<div[^>]*class="[^"]*fs-feed-ad[^"]*"[^>]*>.*?<\/div>/is' => '', // اصلاح‌شده
            '/<figure[^>]*class="[^"]*ad[^"]*"[^>]*>.*?<\/figure>/is' => '', // اصلاح‌شده
            '/Advertisements\s*[\r\n]+.*?(?:<br>|\z)/is' => '',
            '/Information about Iranian doctors.*?(?:<br>|\z)/is' => '',
            '/(\s*\n\s*)+/' => "\n",
            '/(<br\s*\/?>)+/' => '<br>',
            '/<picture[^>]*>.*?(?:<\/picture>|\z)/is' => '',
        ];

        foreach ($patterns as $pattern => $replacement) {
            try {
                $html = preg_replace($pattern, $replacement, $html);
            } catch (\Exception $e) {
                Log::error("خطا در preg_replace برای الگو: {$pattern}", [
                    'error' => $e->getMessage(),
                    'url' => $this->url
                ]);
            }
        }

        return trim(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function translateContent(string $content, TranslationService $translationService): array
    {
        $data = [
            'title' => $this->title ?? 'Untitled',
            'content' => $content,
        ];

        return $translationService->translateArray($data, ['title', 'content']);
    }

    private function saveNews(array $translations): int
    {
        return DB::transaction(function () use ($translations) {
            $titleEn = $translations['title']['en'] ?? 'Untitled';
            $data = [
                'title' => json_encode($translations['title']),
                'content' => json_encode($translations['content']),
                'cover' => null,
                'slug' => Str::slug($titleEn) . '-' . uniqid(),
                'published_at' => now(),
                'source_url' => $this->url,
                'status' => 'published',
                'news_site_id' => $this->siteId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $existingNews = DB::table('news')
                ->where('source_url', $this->url)
                ->first();

            if ($existingNews) {
                DB::table('news')->where('id', $existingNews->id)->update($data);
                Log::info("خبر موجود به‌روزرسانی شد. ID: {$existingNews->id}");
                return $existingNews->id;
            }

            $newsId = DB::table('news')->insertGetId($data);
            Log::info("خبر جدید ایجاد شد. ID: {$newsId}");
            return $newsId;
        });
    }

    private function saveCategory(int $newsId): void
    {
        $exists = DB::table('category_news')
            ->where('news_id', $newsId)
            ->where('category_id', $this->categoryId)
            ->exists();

        if (!$exists) {
            DB::table('category_news')->insert([
                'news_id' => $newsId,
                'category_id' => $this->categoryId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            Log::info("دسته‌بندی برای خبر ID {$newsId} ذخیره شد.");
        }
    }

    private function handleError(\Exception $e): void
    {
        Log::error("خطا در خزش محتوا {$this->url}: {$e->getMessage()}", [
            'exception' => $e,
            'site_id' => $this->siteId,
            'category_id' => $this->categoryId,
            'attempt' => $this->attempts()
        ]);

        if ($this->attempts() >= self::MAX_RETRIES) {
            Log::error("Job شکست خورد پس از {$this->attempts()} تلاش برای URL: {$this->url}");
        } else {
            $this->release(self::RETRY_DELAY);
        }
    }
}
