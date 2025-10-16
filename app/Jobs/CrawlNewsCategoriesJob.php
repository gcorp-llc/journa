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

    public function __construct(int $siteId, int $categoryId, string $url)
    {
        $this->siteId = $siteId;
        $this->categoryId = $categoryId;
        $this->url = $url;
    }

    public function handle()
    {
        try {
            // بارگذاری اطلاعات
            $this->loadSiteAndConfig();

            $html = $this->fetchPage();
            $items = $this->extractLinks($html, $this->config);

            // 👇 بهبود: فیلتر کردن انبوه لینک‌ها (کاهش کوئری‌های دیتابیس)
            $filteredItems = $this->filterValidLinks($items, $this->config);

            if (empty($filteredItems)) {
                Log::warning("هیچ لینک معتبری برای URL پیدا نشد: {$this->url}");
                return;
            }

            $this->dispatchContentJobs($filteredItems);
        } catch (\Exception $e) {
            Log::error("خطا در خزش دسته‌بندی {$this->url}: {$e->getMessage()}", [
                'exception' => $e->getMessage(),
                'site_id' => $this->siteId,
                'category_id' => $this->categoryId
            ]);
            $this->release(self::RETRY_DELAY);
        }
    }

    private function loadSiteAndConfig()
    {
        $site = DB::table('news_sites')->find($this->siteId);
        if (!$site) {
            throw new \Exception("سایت با شناسه {$this->siteId} پیدا نشد.");
        }
        $this->siteName = json_decode($site->name)->en;

        $config = config('crawler.sites.' . $this->siteName);
        if (empty($config['category_selectors']['links'])) {
            throw new \Exception("سلکتور لینک برای سایت {$this->siteName} تعریف نشده است.");
        }
        $this->config = $config;
    }

    private function fetchPage()
    {
        $response = Http::timeout(self::HTTP_TIMEOUT)->get($this->url);
        if (!$response->ok()) {
            throw new \Exception("خطا در دریافت URL: {$this->url}, وضعیت: {$response->status()}");
        }
        return $response->body();
    }

    private function normalizeUrl(string $link): string
    {
        if (empty($link) || !is_string($link)) {
            return '';
        }
        if (!str_starts_with($link, 'http')) {
            $baseUrl = parse_url($this->url, PHP_URL_SCHEME) . '://' . parse_url($this->url, PHP_URL_HOST);
            return rtrim($baseUrl, '/') . '/' . ltrim($link, '/');
        }
        return $link;
    }

    private function extractLinks(string $html, array $config): array
    {
        $crawler = new Crawler($html);

        // استخراج و نرمال‌سازی لینک‌ها
        $links = $crawler->filter($config['category_selectors']['links'])->each(
            fn(Crawler $node) => $this->normalizeUrl($node->attr('href') ?? '')
        );

        // 👇 بهبود: حذف موارد خالی و تکراری به صورت بهینه با استفاده از توابع آرایه‌ای
        $uniqueLinks = array_filter(array_unique($links));

        // تبدیل به فرمت نهایی
        return array_map(fn($link) => ['link' => $link], $uniqueLinks);
    }

    private function filterValidLinks(array $items, array $config): array
    {
        $allLinks = array_column($items, 'link');

        if (empty($allLinks)) {
            return [];
        }

        // کوئری تکی برای یافتن لینک‌های موجود (Bulk Check) -> کاهش شدید کوئری دیتابیس
        $existingUrls = DB::table('news')
            ->whereIn('source_url', $allLinks)
            ->pluck('source_url')
            ->toArray();

        $filteredItems = [];
        foreach ($items as $item) {
            if (empty($item['link'])) {
                continue;
            }

            // حذف لینک‌های تکراری و لینک‌های موجود در دیتابیس
            if (in_array($item['link'], $existingUrls)) {
                continue;
            }

            // اعمال فیلتر کلمات کلیدی
            if (empty($config['category_selectors']['filter']) || $this->containsValidKeywords($item['link'], $config)) {
                $filteredItems[] = $item;
            }
        }

        return $filteredItems;
    }

    private function containsValidKeywords(string $link, array $config)
    {
        if (!preg_match('/^https?:\/\//', $link)) {
            return false;
        }

        if (!empty($config['category_selectors']['filter'])) {
            $linkLower = strtolower($link);
            foreach ((array)$config['category_selectors']['filter'] as $keyword) {
                if (strpos($linkLower, strtolower($keyword)) !== false) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    private function dispatchContentJobs(array $items)
    {
        $maxItems = app()->environment('testing') ? 10 : ($this->config['max_items'] ?? 50);

        Log::info('Dispatching content jobs:', [
            'site_name' => $this->siteName,
            'site_id' => $this->siteId,
            'category_id' => $this->categoryId,
            'items_count' => count($items),
            'max_items' => $maxItems
        ]);

        foreach (array_slice($items, 0, $maxItems) as $item) {
            if (!empty($item['link'])) {
                CrawlNewsContentJob::dispatch(
                    $this->siteName,
                    $this->siteId,
                    $this->categoryId,
                    $item['link'],
                    $this->config['news_selectors'] ?? []
                );
            }
        }
    }
}
