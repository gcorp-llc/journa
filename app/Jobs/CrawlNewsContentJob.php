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

    private const HTTP_TIMEOUT = 15; // افزایش timeout
    private const RETRY_DELAY = 60;
    private const MIN_PARAGRAPH_LENGTH = 50;
    private const MAX_RETRIES = 3;

    private string $siteName;
    private int $siteId;
    private int $categoryId;
    private string $url;
    private ?string $title = null;
    private ?array $config = null;
    private ?string $html = null;

    public $tries = self::MAX_RETRIES;

    /**
     * Create a new job instance.
     */
    public function __construct(string $siteName, int $siteId, int $categoryId, string $url, array $newsSelectors = [])
    {
        $this->siteName = $siteName;
        $this->siteId = $siteId;
        $this->categoryId = $categoryId;
        $this->url = $url;
        $this->config = ['news_selectors' => $newsSelectors];
    }

    /**
     * Execute the job.
     */
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

            Log::info("خبر با موفقیت ذخیره شد. ID: {$newsId}");

            // ارسال job پردازش تصویر با HTML برای جلوگیری از fetch مجدد
            ProcessNewsImageJob::dispatch($newsId, $this->siteName, $this->url, $this->config, $this->html)

                ->delay(now()->addSeconds(2)); // تاخیر کوتاه برای اطمینان از commit شدن transaction

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Get configuration for the site.
     */
    private function getConfig(): array
    {
        $config = config('crawler.sites.' . $this->siteName);
        if (empty($config['news_selectors']['content']) || empty($config['news_selectors']['title'])) {
            throw new \Exception("سلکتورهای محتوا یا عنوان برای سایت {$this->siteName} تعریف نشده‌اند.");
        }
        return $config;
    }

    /**
     * Fetch the page content from the URL.
     */
    private function fetchPage(): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive',
        ])->timeout(self::HTTP_TIMEOUT)->get($this->url);

        if (!$response->ok()) {
            throw new \Exception("خطا در دریافت URL: {$this->url}, وضعیت: {$response->status()}");
        }

        $html = $response->body();
        if (empty($html)) {
            throw new \Exception("محتوای HTML خالی است برای URL: {$this->url}");
        }

        return $html;
    }

    /**
     * Extract content from HTML using selectors.
     */
    private function extractContent(string $html, array $selectors): string
    {
        $crawler = new Crawler($html);

        if ($crawler->filter($selectors['title'])->count()) {
            $this->title = trim($crawler->filter($selectors['title'])->text());
        }

        $this->removeUnwantedElements($crawler);

        $contentHtml = $crawler->filter($selectors['content'])->count()
            ? $crawler->filter($selectors['content'])->html()
            : '';

        $cleanedContent = $this->cleanContent($contentHtml);
        if (strlen(strip_tags($cleanedContent)) < 100) {
            throw new \Exception("محتوای استخراج‌شده بسیار کوتاه است: {$this->url}");
        }

        return $cleanedContent;
    }

    /**
     * Remove unwanted elements from the crawler.
     */
    private function removeUnwantedElements(Crawler $crawler): void
    {
        $unwantedSelectors = [
            'script', 'style', 'iframe', '.ad', '.banner',
            '.advertisement', '[class*="ad-"]', '[id*="ad-"]',
            '.social-share', '.related-posts', '.comments'
        ];

        $crawler->filter(implode(', ', $unwantedSelectors))->each(function (Crawler $node) {
            $domNode = $node->getNode(0);
            if ($domNode && $domNode->parentNode) {
                $domNode->parentNode->removeChild($domNode);
            }
        });
    }

    /**
     * Clean the extracted HTML content.
     */
    private function cleanContent(string $html): string
    {
        $crawler = new Crawler($html);
        $blocks = $crawler->filter('p, div, article')->each(function (Crawler $node) {
            $text = trim($node->html());
            if (strlen(strip_tags($text)) < self::MIN_PARAGRAPH_LENGTH ||
                preg_match('/(advertisement|sponsor|ads)/i', $text)) {
                return '';
            }
            return "<p>{$this->formatText($text)}</p>";
        });

        $content = implode("\n", array_filter($blocks));
        return $this->sanitizeHtml($content);
    }

    /**
     * Format text by adding breaks after periods.
     */
    private function formatText(string $text): string
    {
        return preg_replace('/(\.\s+)/', '.<br>', $text);
    }

    /**
     * Sanitize HTML by removing unwanted tags and scripts.
     */
    private function sanitizeHtml(string $html): string
    {
        $patterns = [
            '/<(?!p|br|img\s*\/?)[^>]+>/i' => '',
            '/<script\b[^>]*>(.*?)<\/script>/is' => '',
            '/freestar\.queue\.push\s*\(.*?\);/is' => '',
            '/document\.querySelectorAll\s*\(.*?\);/is' => '',
            '/window\.fsadcount.*?;/is' => '',
            '/Math\.random\s*\(.*?\)/is' => '',
            '/<[^>]*class=".*?fs-feed-ad.*?"[^>]*>.*?<\/[^>]+>/is' => '',
            '/Advertisements\s*[\r\n]+.*?(?:<br>|\z)/is' => '',
            '/Information about Iranian doctors.*?(?:<br>|\z)/is' => '',
            '/(\s*\n\s*)+/' => "\n",
            '/(<br\s*\/?>)+/' => '<br>',
            '/<picture[^>]*>.*?(?:<\/picture>|\z)/is' => '',
            '/<figure[^>]*class=".*?ad.*?"[^>]*>.*?<\/figure>/is' => '',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html);
        }

        return trim(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Translate content to Persian and Arabic.
     */
    private function translateContent(string $content, TranslationService $translationService): array
    {
        $data = [
            'title' => $this->title ?? 'Untitled',
            'content' => $content,
        ];

        return $translationService->translateArray($data, ['title', 'content']);
    }

    /**
     * Save news to the database.
     */
    private function saveNews(array $translations): int
    {
        return DB::transaction(function () use ($translations) {
            $titleEn = $translations['title']['en'] ?? 'Untitled';
            $data = [
                'title' => json_encode($translations['title']),
                'content' => json_encode($translations['content']),
                'cover' => null, // Initially null, will be updated by image job
                'slug' => Str::slug($titleEn) . '-' . uniqid(),
                'published_at' => now(),
                'source_url' => $this->url,
                'status' => 'published',
                'news_site_id' => $this->siteId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // بررسی وجود خبر با URL مشابه
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

    /**
     * Save category association for the news.
     */
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

    /**
     * Handle errors during job execution.
     */
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
