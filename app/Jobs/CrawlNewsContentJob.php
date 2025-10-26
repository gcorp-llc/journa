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
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';

    private string $siteName;
    private int $siteId;
    private int $categoryId;
    private string $url;
    private ?string $title = null;
    private ?array $config = null;
    private ?string $html = null;
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
            Log::info("🚀 [شروع پردازش محتوای خبر]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
                'site_id' => $this->siteId,
                'category_id' => $this->categoryId,
                'url' => $this->url,
                'attempt' => $this->attempts(),
                'max_retries' => self::MAX_RETRIES,
                'timestamp' => now()->toDateTimeString(),
            ]);

            if (empty($this->config['news_selectors'])) {
                $this->loadConfig();
            }

            Log::debug("⚙️  [کانفیگ سایت بارگذاری شد]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
                'selectors_count' => count($this->config['news_selectors']),
            ]);

            $this->html = $this->fetchPage();

            Log::info("📥 [صفحه HTML با موفقیت دریافت شد]", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'html_size_kb' => round(strlen($this->html) / 1024, 2),
                'html_lines' => count(explode("\n", $this->html)),
            ]);

            $content = $this->extractContent($this->html, $this->config['news_selectors']);

            Log::info("📄 [محتوا با موفقیت استخراج شد]", [
                'job_id' => $this->jobId,
                'title' => $this->title,
                'content_length_chars' => strlen($content),
                'content_length_without_tags' => strlen(strip_tags($content)),
                'paragraph_count' => substr_count($content, '<p>'),
                'heading_count' => substr_count($content, '<h') - substr_count($content, '</h'),
            ]);

            if (empty($this->title)) {
                throw new \Exception("عنوان خبر پیدا نشد. سلکتور: {$this->config['news_selectors']['title']}");
            }

            Log::debug("🌐 [شروع ترجمه محتوا]", [
                'job_id' => $this->jobId,
                'title_to_translate' => substr($this->title, 0, 50) . '...',
            ]);

            $translations = $this->translateContent($content, $translationService);

            Log::info("✅ [محتوا ترجمه شد]", [
                'job_id' => $this->jobId,
                'languages' => array_keys($translations['title']),
                'title_translated_to' => implode(', ', array_keys($translations['title'])),
            ]);

            $newsId = $this->saveNews($translations);

            Log::info("💾 [خبر در دیتابیس ذخیره شد]", [
                'job_id' => $this->jobId,
                'news_id' => $newsId,
                'url' => $this->url,
                'title_en' => $translations['title']['en'] ?? 'نامعلوم',
            ]);

            $this->saveCategory($newsId);

            Log::info("🏷️  [دسته‌بندی‌های خبر ذخیره شدند]", [
                'job_id' => $this->jobId,
                'news_id' => $newsId,
                'category_id' => $this->categoryId,
            ]);

            ProcessNewsImageJob::dispatch($newsId, $this->siteName, $this->url, $this->config, $this->html, $translations['title']['en'] ?? 'news')
                ->delay(now()->addSeconds(3));

            Log::info("📮 [جاب پردازش تصویر ارسال شد]", [
                'job_id' => $this->jobId,
                'news_id' => $newsId,
                'dispatch_delay_seconds' => 3,
            ]);

            $executionTime = round((microtime(true) - $this->startTime) * 1000, 2);

            Log::info("✨ [تکمیل موفقیت‌آمیز CrawlNewsContentJob]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
                'site_id' => $this->siteId,
                'category_id' => $this->categoryId,
                'url' => $this->url,
                'news_id' => $newsId,
                'content_length' => strlen(strip_tags($content)),
                'paragraph_count' => substr_count($content, '<p>'),
                'execution_time_ms' => $executionTime,
                'timestamp' => now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    private function loadConfig(): void
    {
        try {
            $config = config('crawler.sites.' . $this->siteName);

            if (empty($config)) {
                throw new \Exception("کانفیگ کرولر برای سایت '{$this->siteName}' یافت نشد");
            }

            if (empty($config['news_selectors']['content'])) {
                throw new \Exception("سلکتور محتوا برای سایت '{$this->siteName}' تعریف نشده");
            }

            if (empty($config['news_selectors']['title'])) {
                throw new \Exception("سلکتور عنوان برای سایت '{$this->siteName}' تعریف نشده");
            }

            Log::debug("✅ [کانفیگ سایت بارگذاری شد]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
                'config_keys' => array_keys($config),
            ]);

            $this->config = $config;

        } catch (\Exception $e) {
            Log::error("❌ [خطا در بارگذاری کانفیگ سایت]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    private function fetchPage(): string
    {
        try {
            Log::debug("🌐 [درخواست HTTP برای دریافت صفحه]", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'timeout_seconds' => self::HTTP_TIMEOUT,
                'user_agent' => substr(self::USER_AGENT, 0, 50) . '...',
            ]);

            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9,fa;q=0.7',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
                'Referer' => 'https://www.google.com/',
            ])->timeout(self::HTTP_TIMEOUT)->get($this->url);

            Log::debug("📊 [پاسخ HTTP دریافت شد]", [
                'job_id' => $this->jobId,
                'status_code' => $response->status(),
                'status_reason' => $response->reason(),
                'response_headers' => array_keys($response->headers()),
            ]);

            if (!$response->ok()) {
                throw new \Exception(
                    "خطا در دریافت URL. وضعیت HTTP: {$response->status()} ({$response->reason()})"
                );
            }

            $html = $response->body();

            if (empty($html)) {
                throw new \Exception("محتوای HTML خالی است");
            }

            Log::debug("✅ [محتوای HTML معتبر است]", [
                'job_id' => $this->jobId,
                'is_html' => preg_match('/<html|<body|<!doctype/i', $html) ? 'بله' : 'خیر',
            ]);

            return $html;

        } catch (\Exception $e) {
            Log::error("❌ [خطا در دریافت صفحه]", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);
            throw $e;
        }
    }

    private function extractContent(string $html, array $selectors): string
    {
        try {
            $crawler = new Crawler($html);

            Log::debug("🔍 [شروع استخراج محتوا]", [
                'job_id' => $this->jobId,
                'title_selector' => $selectors['title'],
                'content_selector' => $selectors['content'],
            ]);

            // استخراج عنوان
            try {
                $titleNodeCount = $crawler->filter($selectors['title'])->count();
                Log::debug("📋 [تعداد تگ‌های عنوان]", [
                    'job_id' => $this->jobId,
                    'title_selector' => $selectors['title'],
                    'found_count' => $titleNodeCount,
                ]);

                if ($titleNodeCount > 0) {
                    $this->title = trim($crawler->filter($selectors['title'])->first()->text());
                    Log::debug("✅ [عنوان استخراج شد]", [
                        'job_id' => $this->jobId,
                        'title' => substr($this->title, 0, 100),
                        'title_length' => strlen($this->title),
                    ]);
                } else {
                    Log::warning("⚠️  [عنوان با سلکتور یافت نشد]", [
                        'job_id' => $this->jobId,
                        'selector' => $selectors['title'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("⚠️  [خطا در استخراج عنوان]", [
                    'job_id' => $this->jobId,
                    'error_message' => $e->getMessage(),
                ]);
            }

            // حذف المنت‌های عمومی ناخواسته
            $this->removeUnwantedElements($crawler);

            Log::debug("🧹 [المنت‌های عمومی ناخواسته حذف شدند]", [
                'job_id' => $this->jobId,
            ]);

            // حذف المنت‌های خاص سایت
            if (!empty($selectors['unwanted_content_selectors'])) {
                $removedCount = $this->removeCustomUnwantedElements($crawler, $selectors['unwanted_content_selectors']);
                Log::debug("🗑️  [المنت‌های سایت‌خاص حذف شدند]", [
                    'job_id' => $this->jobId,
                    'removed_count' => $removedCount,
                    'selectors_count' => count($selectors['unwanted_content_selectors']),
                ]);
            }

            // استخراج محتوا
            $contentNodeCount = $crawler->filter($selectors['content'])->count();
            Log::debug("📍 [تعداد گره‌های محتوا]", [
                'job_id' => $this->jobId,
                'content_selector' => $selectors['content'],
                'found_count' => $contentNodeCount,
            ]);

            $contentHtml = $this->extractContentParagraphs($crawler, $selectors['content']);

            if (empty($contentHtml)) {
                Log::error("❌ [محتوای قابل استخراج یافت نشد]", [
                    'job_id' => $this->jobId,
                    'content_selector' => $selectors['content'],
                    'url' => $this->url,
                ]);
                throw new \Exception("محتوای قابل استخراج (p, h) یافت نشد");
            }

            $cleanedContent = $this->sanitizeHtml($contentHtml);

            $cleanedLength = strlen(strip_tags($cleanedContent));
            if ($cleanedLength < 100) {
                Log::error("❌ [محتوای استخراج‌شده بسیار کوتاه است]", [
                    'job_id' => $this->jobId,
                    'content_length' => $cleanedLength,
                    'minimum_required' => 100,
                ]);
                throw new \Exception("محتوای استخراج‌شده بسیار کوتاه است ({$cleanedLength} کاراکتر)");
            }

            Log::info("📝 [محتوا با موفقیت تمیز و تایید شد]", [
                'job_id' => $this->jobId,
                'final_content_length' => $cleanedLength,
                'paragraph_count' => substr_count($cleanedContent, '<p>'),
            ]);

            return $cleanedContent;

        } catch (\Exception $e) {
            Log::error("💥 [خطای حرج در استخراج محتوا]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'url' => $this->url,
                'html_size' => strlen($html),
            ]);
            throw $e;
        }
    }

    private function extractContentParagraphs(Crawler $crawler, string $contentSelector): string
    {
        $contentHtml = '';
        $paragraphsFound = 0;
        $paragraphsFiltered = 0;

        try {
            $crawler->filter($contentSelector . ' p, ' . $contentSelector . ' h1, ' . $contentSelector . ' h2, ' . $contentSelector . ' h3, ' . $contentSelector . ' h4, ' . $contentSelector . ' h5, ' . $contentSelector . ' h6')
                ->each(function (Crawler $node) use (&$contentHtml, &$paragraphsFound, &$paragraphsFiltered) {
                    $paragraphsFound++;
                    $tag = $node->nodeName();
                    $text = trim($node->html());
                    $plainText = strip_tags($text);

                    // فیلتر کردن بر اساس طول و محتوا
                    if (strlen($plainText) >= self::MIN_PARAGRAPH_LENGTH &&
                        !preg_match('/(advertisement|sponsor|ads|subscribe|sign up|©|copyright)/i', $plainText)) {
                        $contentHtml .= "<{$tag}>{$text}</{$tag}>\n";
                    } else {
                        $paragraphsFiltered++;
                    }
                });

            Log::debug("📊 [آمار پاراگراف‌ها]", [
                'job_id' => $this->jobId,
                'total_found' => $paragraphsFound,
                'accepted' => $paragraphsFound - $paragraphsFiltered,
                'filtered_out' => $paragraphsFiltered,
            ]);

        } catch (\Exception $e) {
            Log::warning("⚠️  [خطا در استخراج پاراگراف‌ها]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
            ]);
        }

        return $contentHtml;
    }

    private function removeUnwantedElements(Crawler $crawler): void
    {
        $unwantedSelectors = [
            'script', 'style', 'iframe', 'nav', 'footer',
            '.ad', '.banner', '.advertisement',
            '[class*="ad-"]', '[id*="ad-"]',
            '[data-testid*="ad-"]',
            '.social-share', '.related-posts', '.comments',
            '.Component-video-0', '.Component-image-0', '.Component-caption-0',
            '.inline-content', '.promo-content', '.ad-block',
            'figure[class*="ad"]', 'div[class*="fs-feed-ad"]',
            'aside'
        ];

        try {
            $removedCount = 0;
            $crawler->filter(implode(', ', $unwantedSelectors))->each(function (Crawler $node) use (&$removedCount) {
                $domNode = $node->getNode(0);
                if ($domNode && $domNode->parentNode) {
                    $domNode->parentNode->removeChild($domNode);
                    $removedCount++;
                }
            });

            Log::debug("🗑️  [المنت‌های عمومی ناخواسته حذف شدند]", [
                'job_id' => $this->jobId,
                'removed_count' => $removedCount,
            ]);

        } catch (\Exception $e) {
            Log::warning("⚠️  [خطا در حذف المنت‌های عمومی]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function removeCustomUnwantedElements(Crawler $crawler, array $selectors): int
    {
        $removedCount = 0;

        try {
            $unwantedSelectors = implode(', ', array_filter(array_map('trim', $selectors)));

            if (!empty($unwantedSelectors)) {
                $contentNode = $crawler->filter($selectors['content'] ?? 'body');
                if ($contentNode->count() > 0) {
                    $contentNode->filter($unwantedSelectors)->each(function (Crawler $node) use (&$removedCount) {
                        $domNode = $node->getNode(0);
                        if ($domNode && $domNode->parentNode) {
                            $domNode->parentNode->removeChild($domNode);
                            $removedCount++;
                        }
                    });
                }
            }
        } catch (\Exception $e) {
            Log::warning("⚠️  [خطا در حذف المنت‌های خاص سایت]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
            ]);
        }

        return $removedCount;
    }

    private function sanitizeHtml(string $html): string
    {
        try {
            $patterns = [
                '#<(?!\/?(p|br|strong|em|h[1-6]))[^>]+>#i' => '',
                '#<a[^>]*>(.*?)</a>#is' => '$1',
                '#<script\b[^>]*>.*?</script>#is' => '',
                '#<picture[^>]*>.*?</picture>#is' => '',
                '#freestar\.queue\.push\s*\(.*?\);#is' => '',
                '#document\.querySelectorAll\s*\(.*?\);#is' => '',
                '#window\.fsadcount.*?;#is' => '',
                '#Math\.random\s*\(.*?\)#is' => '',
                '#<div[^>]*class="[^"]*fs-feed-ad[^"]*"[^>]*>.*?</div>#is' => '',
                '#<figure[^>]*class="[^"]*ad[^"]*"[^>]*>.*?</figure>#is' => '',
                '#Advertisements\s*[\r\n]+.*?(?:<br>|\z)#is' => '',
                '#Information about Iranian doctors.*?(?:<br>|\z)#is' => '',
                '#(\s*\n\s*)+#' => "\n",
                '#(<br\s*\/?>\s*)+#' => '<br>',
            ];

            $processedCount = 0;
            foreach ($patterns as $pattern => $replacement) {
                try {
                    $oldLength = strlen($html);
                    $html = preg_replace($pattern, $replacement, $html);

                    if (strlen($html) !== $oldLength) {
                        $processedCount++;
                        Log::debug("🧹 [الگو اعمال شد]", [
                            'job_id' => $this->jobId,
                            'pattern' => substr($pattern, 0, 40),
                            'size_reduced_by' => $oldLength - strlen($html),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("❌ [خطا در preg_replace]", [
                        'job_id' => $this->jobId,
                        'pattern' => $pattern,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }

            Log::info("✨ [تمیزکاری HTML]", [
                'job_id' => $this->jobId,
                'patterns_applied' => $processedCount,
                'final_size_kb' => round(strlen($html) / 1024, 2),
            ]);

            return trim(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        } catch (\Exception $e) {
            Log::error("❌ [خطا در تمیزکاری HTML]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
            ]);
            return $html;
        }
    }

    private function translateContent(string $content, TranslationService $translationService): array
    {
        try {
            $data = [
                'title' => $this->title ?? 'Untitled',
                'content' => $content,
            ];

            Log::debug("🌐 [شروع ترجمه]", [
                'job_id' => $this->jobId,
                'title' => substr($data['title'], 0, 50),
                'content_length' => strlen($data['content']),
            ]);

            $translations = $translationService->translateArray($data, ['title', 'content']);

            Log::info("✅ [ترجمه کامل شد]", [
                'job_id' => $this->jobId,
                'languages_translated' => array_keys($translations['title']),
                'title_languages' => implode(', ', array_keys($translations['title'])),
            ]);

            return $translations;

        } catch (\Exception $e) {
            Log::error("❌ [خطا در ترجمه محتوا]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
                'title' => $this->title ?? 'نامعلوم',
            ]);
            throw $e;
        }
    }

    private function saveNews(array $translations): int
    {
        try {
            return DB::transaction(function () use ($translations) {
                $titleEn = $translations['title']['en'] ?? 'Untitled';
                $slug = Str::slug($titleEn);

                if (empty($slug)) {
                    $slug = 'news-' . uniqid();
                }

                Log::debug("💾 [آماده‌سازی داده برای ذخیره]", [
                    'job_id' => $this->jobId,
                    'url' => $this->url,
                    'slug' => $slug,
                    'title_en' => substr($titleEn, 0, 50),
                ]);

                $data = [
                    'title' => json_encode($translations['title']),
                    'content' => json_encode($translations['content']),
                    'cover' => null,
                    'slug' => $slug,
                    'published_at' => now(),
                    'source_url' => $this->url,
                    'status' => 'published',
                    'news_site_id' => $this->siteId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                DB::table('news')->updateOrInsert(
                    ['source_url' => $this->url],
                    $data
                );

                $news = DB::table('news')->where('source_url', $this->url)->first();

                if (!$news) {
                    throw new \Exception("خبر پس از ذخیره یافت نشد");
                }

                Log::info("✅ [خبر ذخیره/آپدیت شد]", [
                    'job_id' => $this->jobId,
                    'news_id' => $news->id,
                    'action' => $news->created_at === $news->updated_at ? 'created' : 'updated',
                ]);

                return $news->id;
            });

        } catch (\Exception $e) {
            Log::error("❌ [خطا در ذخیره خبر]", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    private function saveCategory(int $newsId): void
    {
        try {
            DB::table('category_news')->insertOrIgnore([
                'news_id' => $newsId,
                'category_id' => $this->categoryId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info("✅ [دسته‌بندی ذخیره/نادیده‌گرفته شد]", [
                'job_id' => $this->jobId,
                'news_id' => $newsId,
                'category_id' => $this->categoryId,
            ]);

        } catch (\Exception $e) {
            Log::error("❌ [خطا در ذخیره دسته‌بندی]", [
                'job_id' => $this->jobId,
                'news_id' => $newsId,
                'category_id' => $this->categoryId,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function handleError(\Exception $e): void
    {
        $executionTime = round((microtime(true) - $this->startTime) * 1000, 2);
        $currentAttempt = $this->attempts();
        $nextRetryDelay = self::RETRY_DELAY * $currentAttempt;

        Log::error("❌ [خطا در CrawlNewsContentJob]", [
            'job_id' => $this->jobId,
            'site_name' => $this->siteName,
            'site_id' => $this->siteId,
            'category_id' => $this->categoryId,
            'url' => $this->url,
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'error_class' => class_basename($e),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'attempt' => $currentAttempt,
            'max_retries' => self::MAX_RETRIES,
            'execution_time_ms' => $executionTime,
        ]);

        if ($currentAttempt >= self::MAX_RETRIES) {
            Log::error("💥 [Job ناپذیر شد بعد از تمام تلاش‌ها]", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'total_attempts' => $currentAttempt,
                'total_execution_time_ms' => $executionTime,
                'timestamp' => now()->toDateTimeString(),
            ]);

            $this->fail($e);
        } else {
            Log::warning("⏳ [جاب برای تلاش مجدد منتشر شد]", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'current_attempt' => $currentAttempt,
                'next_retry_in_seconds' => $nextRetryDelay,
                'execution_time_ms' => $executionTime,
            ]);

            $this->release($nextRetryDelay);
        }
    }
}
