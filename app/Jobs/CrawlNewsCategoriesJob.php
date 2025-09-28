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
            $site = $this->getSiteInfo();
            $this->config = $this->getConfig();
            $html = $this->fetchPage();
            $items = $this->extractLinks($html, $this->config);
            $filteredItems = $this->filterValidLinks($items, $this->config);

            if (empty($filteredItems)) {
                Log::warning("هیچ لینک معتبری برای URL پیدا نشد: {$this->url}");
                return;
            }

            $this->dispatchContentJobs($filteredItems);
        } catch (\Exception $e) {
            Log::error("خطا در خزش دسته‌بندی {$this->url}: {$e->getMessage()}", [
                'exception' => $e,
                'site_id' => $this->siteId,
                'category_id' => $this->categoryId
            ]);
            $this->release(self::RETRY_DELAY);
        }
    }

    private function getSiteInfo()
    {
        $site = DB::table('news_sites')->find($this->siteId);
        if (!$site) {
            throw new \Exception("سایت با شناسه {$this->siteId} پیدا نشد.");
        }
        $this->siteName = json_decode($site->name)->en;
        return $site;
    }

    private function getConfig()
    {
        $config = config('crawler.sites.' . $this->siteName);
        if (empty($config['category_selectors']['links'])) {
            throw new \Exception("سلکتور لینک برای سایت {$this->siteName} تعریف نشده است.");
        }
        return $config;
    }

    private function fetchPage()
    {
        $response = Http::timeout(self::HTTP_TIMEOUT)->get($this->url);
        if (!$response->ok()) {
            throw new \Exception("خطا در دریافت URL: {$this->url}, وضعیت: {$response->status()}");
        }
        return $response->body();
    }

    private function normalizeUrl(string $link)
    {
        if (empty($link) || !is_string($link)) {
            return '';
        }
        if (!str_starts_with($link, 'http')) {
            return rtrim($this->url, '/') . '/' . ltrim($link, '/');
        }
        return $link;
    }

    private function extractLinks(string $html, array $config)
    {
        $crawler = new Crawler($html);
        $links = $crawler->filter($config['category_selectors']['links'])->each(
            fn(Crawler $node) => ['link' => $this->normalizeUrl($node->attr('href') ?? '')]
        );

        $uniqueLinks = [];
        $seenUrls = [];

        foreach ($links as $item) {
            if (!empty($item['link']) && !isset($seenUrls[$item['link']])) {
                $seenUrls[$item['link']] = true;
                $uniqueLinks[] = $item;
            }
        }

        return $uniqueLinks;
    }

    private function filterValidLinks(array $items, array $config)
    {
        $filteredItems = array_filter($items, function ($item) use ($config) {
            if (empty($item['link'])) {
                return false;
            }

            $exists = DB::table('news')->where('source_url', $item['link'])->exists();
            if ($exists) {
                return false;
            }

            if (empty($config['category_selectors']['filter'])) {
                return true;
            }

            return $this->containsValidKeywords($item['link'], $config);
        });

        return array_values($filteredItems);
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
        $rateLimit = $this->config['rate_limit'] ?? 2;
        $delaySeconds = app()->environment('testing') ? 0 : rand(1, $rateLimit * 5);

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
                )->delay(now()->addSeconds($delaySeconds));
            }
        }
    }
}
