<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class CrawlNewsCategoriesJob implements ShouldQueue
{
    use Queueable;

    private const HTTP_TIMEOUT = 10;
    private const RETRY_DELAY = 60;

    private int $siteId;
    private int $categoryId;
    private string $url;
    private string $siteName;
    private ?array $config = null;
    private string $jobId;
    private float $startTime;

    public function __construct(int $siteId, int $categoryId, string $url)
    {
        $this->siteId = $siteId;
        $this->categoryId = $categoryId;
        $this->url = $url;
        $this->jobId = uniqid('crawl_news_', true);
        $this->startTime = microtime(true);
    }

    public function handle()
    {
        try {
            Log::info("🚀 [شروع خزش محتوای دسته‌بندی]", [
                'job_id' => $this->jobId,
                'site_id' => $this->siteId,
                'category_id' => $this->categoryId,
                'url' => $this->url,
                'timestamp' => now()->toDateTimeString(),
            ]);

            // بارگذاری اطلاعات
            $this->loadSiteAndConfig();

            Log::debug("✅ [اطلاعات سایت و کانفیگ بارگذاری شد]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
            ]);

            $html = $this->fetchPage();

            Log::info("📥 [صفحه با موفقیت دریافت شد]", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'html_size_kb' => round(strlen($html) / 1024, 2),
            ]);

            $items = $this->extractLinks($html, $this->config);

            Log::info("🔗 [لینک‌ها استخراج شدند]", [
                'job_id' => $this->jobId,
                'total_links_found' => count($items),
                'unique_links' => count(array_unique(array_column($items, 'link'))),
            ]);

            // فیلتر کردن لینک‌های معتبر
            $filteredItems = $this->filterValidLinks($items, $this->config);

            Log::info("🔍 [لینک‌های فیلتر شدند]", [
                'job_id' => $this->jobId,
                'total_links_before_filter' => count($items),
                'valid_links_after_filter' => count($filteredItems),
                'removed_links' => count($items) - count($filteredItems),
            ]);

            if (empty($filteredItems)) {
                Log::warning("⚠️  [هیچ لینک معتبری برای خزش پیدا نشد]", [
                    'job_id' => $this->jobId,
                    'url' => $this->url,
                    'site_name' => $this->siteName,
                    'category_id' => $this->categoryId,
                ]);
                return;
            }

            $this->dispatchContentJobs($filteredItems);

            $executionTime = round((microtime(true) - $this->startTime) * 1000, 2);

            Log::info("✨ [تکمیل موفقیت‌آمیز CrawlNewsCategoriesJob]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
                'site_id' => $this->siteId,
                'category_id' => $this->categoryId,
                'url' => $this->url,
                'total_links_extracted' => count($items),
                'valid_links_dispatched' => count($filteredItems),
                'execution_time_ms' => $executionTime,
                'timestamp' => now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $this->startTime) * 1000, 2);

            Log::error("💥 [خطا در CrawlNewsCategoriesJob]", [
                'job_id' => $this->jobId,
                'site_id' => $this->siteId,
                'category_id' => $this->categoryId,
                'url' => $this->url,
                'site_name' => $this->siteName ?? 'نامعلوم',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => class_basename($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'stack_trace' => explode("\n", $e->getTraceAsString()),
                'execution_time_ms' => $executionTime,
                'will_retry_in_seconds' => self::RETRY_DELAY,
                'timestamp' => now()->toDateTimeString(),
            ]);

            $this->release(self::RETRY_DELAY);
        }
    }

    private function loadSiteAndConfig()
    {
        try {
            $site = DB::table('news_sites')->find($this->siteId);
            if (!$site) {
                throw new \Exception("سایت با شناسه {$this->siteId} در دیتابیس پیدا نشد");
            }

            $this->siteName = json_decode($site->name)->en ?? 'نامعلوم';

            Log::debug("📝 [جزئیات سایت بارگذاری شد]", [
                'job_id' => $this->jobId,
                'site_id' => $this->siteId,
                'site_name' => $this->siteName,
                'site_config_keys' => array_keys((array)$site),
            ]);

            $config = config('crawler.sites.' . $this->siteName);

            if (empty($config)) {
                throw new \Exception("کانفیگ کرولر برای سایت '{$this->siteName}' یافت نشد");
            }

            if (empty($config['category_selectors']['links'])) {
                throw new \Exception("سلکتور لینک برای سایت '{$this->siteName}' تعریف نشده است");
            }

            Log::debug("⚙️  [کانفیگ کرولر بارگذاری شد]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
                'config_sections' => array_keys($config),
                'link_selector' => $config['category_selectors']['links'],
            ]);

            $this->config = $config;

        } catch (\Exception $e) {
            Log::error("❌ [خطا در بارگذاری اطلاعات سایت و کانفیگ]", [
                'job_id' => $this->jobId,
                'site_id' => $this->siteId,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    private function fetchPage()
    {
        try {
            Log::debug("🌐 [درخواست HTTP برای دریافت صفحه]", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'timeout_seconds' => self::HTTP_TIMEOUT,
            ]);

            $response = Http::timeout(self::HTTP_TIMEOUT)->get($this->url);

            Log::debug("📊 [پاسخ HTTP دریافت شد]", [
                'job_id' => $this->jobId,
                'status_code' => $response->status(),
                'response_size_kb' => round(strlen($response->body()) / 1024, 2),
            ]);

            if (!$response->ok()) {
                throw new \Exception(
                    "خطا در دریافت URL. وضعیت HTTP: {$response->status()}, پیام: {$response->reason()}"
                );
            }

            return $response->body();

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

    private function normalizeUrl(string $link): string
    {
        if (empty($link) || !is_string($link)) {
            return '';
        }

        if (!str_starts_with($link, 'http')) {
            $scheme = parse_url($this->url, PHP_URL_SCHEME);
            $host = parse_url($this->url, PHP_URL_HOST);

            if (!$scheme || !$host) {
                Log::warning("⚠️  [نتوانست URL پایه را تجزیه کند]", [
                    'job_id' => $this->jobId,
                    'base_url' => $this->url,
                    'relative_link' => $link,
                ]);
                return '';
            }

            $baseUrl = $scheme . '://' . $host;
            return rtrim($baseUrl, '/') . '/' . ltrim($link, '/');
        }

        return $link;
    }

    private function extractLinks(string $html, array $config): array
    {
        try {
            $crawler = new Crawler($html);
            $linkSelector = $config['category_selectors']['links'];

            Log::debug("🔎 [شروع استخراج لینک‌ها]", [
                'job_id' => $this->jobId,
                'selector' => $linkSelector,
            ]);

            try {
                $links = $crawler->filter($linkSelector)->each(
                    fn(Crawler $node) => $this->normalizeUrl($node->attr('href') ?? '')
                );
            } catch (\InvalidArgumentException $e) {
                throw new \Exception("سلکتور CSS نامعتبر: '{$linkSelector}'. خطا: {$e->getMessage()}");
            }

            Log::debug("📋 [لینک‌های خام استخراج شدند]", [
                'job_id' => $this->jobId,
                'total_raw_links' => count($links),
                'sample_links' => array_slice($links, 0, 3),
            ]);

            // حذف موارد خالی و تکراری
            $uniqueLinks = array_filter(array_unique($links));

            Log::info("✔️  [لینک‌های منحصر به فرد تعیین شدند]", [
                'job_id' => $this->jobId,
                'total_raw_links' => count($links),
                'unique_links' => count($uniqueLinks),
                'duplicate_links_removed' => count($links) - count($uniqueLinks),
                'empty_links_removed' => count(array_filter($links)) - count($uniqueLinks),
            ]);

            return array_map(fn($link) => ['link' => $link], $uniqueLinks);

        } catch (\Exception $e) {
            Log::error("❌ [خطا در استخراج لینک‌ها]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
                'html_size' => strlen($html),
                'selector' => $config['category_selectors']['links'] ?? 'نامعلوم',
            ]);
            throw $e;
        }
    }

    private function filterValidLinks(array $items, array $config): array
    {
        try {
            $allLinks = array_column($items, 'link');

            if (empty($allLinks)) {
                Log::warning("⚠️  [آرایه لینک‌ها خالی است]", [
                    'job_id' => $this->jobId,
                ]);
                return [];
            }

            Log::debug("🔍 [شروع بررسی لینک‌های موجود در دیتابیس]", [
                'job_id' => $this->jobId,
                'total_links_to_check' => count($allLinks),
            ]);

            // کوئری تکی برای یافتن لینک‌های موجود
            $existingUrls = DB::table('news')
                ->whereIn('source_url', $allLinks)
                ->pluck('source_url')
                ->toArray();

            Log::info("📊 [بررسی لینک‌های موجود]", [
                'job_id' => $this->jobId,
                'total_links_checked' => count($allLinks),
                'existing_links_in_db' => count($existingUrls),
                'new_potential_links' => count($allLinks) - count($existingUrls),
            ]);

            $filteredItems = [];
            $duplicateCount = 0;
            $filterRejectedCount = 0;

            foreach ($items as $item) {
                if (empty($item['link'])) {
                    continue;
                }

                // حذف لینک‌های تکراری
                if (in_array($item['link'], $existingUrls)) {
                    $duplicateCount++;
                    continue;
                }

                // اعمال فیلتر کلمات کلیدی
                if (empty($config['category_selectors']['filter']) || $this->containsValidKeywords($item['link'], $config)) {
                    $filteredItems[] = $item;
                } else {
                    $filterRejectedCount++;
                }
            }

            Log::info("🎯 [خلاصه فیلتر کردن لینک‌ها]", [
                'job_id' => $this->jobId,
                'total_items' => count($items),
                'duplicates_removed' => $duplicateCount,
                'filter_rejected' => $filterRejectedCount,
                'valid_items_remaining' => count($filteredItems),
                'filter_pattern' => $config['category_selectors']['filter'] ?? 'بدون فیلتر',
            ]);

            return $filteredItems;

        } catch (\Exception $e) {
            Log::error("❌ [خطا در فیلتر کردن لینک‌ها]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
                'items_count' => count($items),
            ]);
            throw $e;
        }
    }

    private function containsValidKeywords(string $link, array $config): bool
    {
        if (!preg_match('/^https?:\/\//', $link)) {
            Log::debug("⛔ [لینک با پروتکل معتبر نیست]", [
                'job_id' => $this->jobId,
                'link' => $link,
            ]);
            return false;
        }

        if (!empty($config['category_selectors']['filter'])) {
            $linkLower = strtolower($link);
            $filter = (array)$config['category_selectors']['filter'];

            foreach ($filter as $keyword) {
                if (strpos($linkLower, strtolower($keyword)) !== false) {
                    Log::debug("✅ [لینک از فیلتر عبور کرد]", [
                        'job_id' => $this->jobId,
                        'link' => $link,
                        'matched_keyword' => $keyword,
                    ]);
                    return true;
                }
            }

            Log::debug("❌ [لینک فیلتر را پاس نکرد]", [
                'job_id' => $this->jobId,
                'link' => $link,
                'filter_keywords' => $filter,
            ]);
            return false;
        }

        return true;
    }

    private function dispatchContentJobs(array $items)
    {
        try {
//            $maxItems = app()->environment('testing') ? 10 : ($this->config['max_items'] ?? 50);
            $maxItems = 50;

            $itemsToDispatch = array_slice($items, 0, $maxItems);

            Log::info("📤 [شروع ارسال جاب‌های محتوا]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
                'site_id' => $this->siteId,
                'category_id' => $this->categoryId,
                'total_items' => count($items),
                'items_to_dispatch' => count($itemsToDispatch),
                'max_items_config' => $maxItems,
                'items_skipped' => count($items) - count($itemsToDispatch),
            ]);

            $dispatchedCount = 0;
            $errorCount = 0;

            foreach ($itemsToDispatch as $index => $item) {
                if (!empty($item['link'])) {
                    try {
                        Log::debug("📮 [ارسال جاب برای لینک]", [
                            'job_id' => $this->jobId,
                            'item_index' => $index + 1,
                            'link' => $item['link'],
                        ]);

                        CrawlNewsContentJob::dispatch(
                            $this->siteName,
                            $this->siteId,
                            $this->categoryId,
                            $item['link'],
                            $this->config['news_selectors'] ?? []
                        );

                        $dispatchedCount++;

                    } catch (\Exception $e) {
                        $errorCount++;
                        Log::error("❌ [خطا در ارسال جاب محتوا]", [
                            'job_id' => $this->jobId,
                            'link' => $item['link'],
                            'error_message' => $e->getMessage(),
                        ]);
                    }
                }
            }

            Log::info("✅ [خلاصه ارسال جاب‌های محتوا]", [
                'job_id' => $this->jobId,
                'site_name' => $this->siteName,
                'successfully_dispatched' => $dispatchedCount,
                'failed_dispatches' => $errorCount,
                'total_attempted' => count($itemsToDispatch),
                'success_rate' => round(($dispatchedCount / count($itemsToDispatch)) * 100, 2) . '%',
            ]);

        } catch (\Exception $e) {
            Log::error("💥 [خطای حرج در ارسال جاب‌های محتوا]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
                'items_count' => count($items),
            ]);
            throw $e;
        }
    }
}
