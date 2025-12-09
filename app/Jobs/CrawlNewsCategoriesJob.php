<?php

namespace App\Jobs;

use App\Traits\InteractsWithHttp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class CrawlNewsCategoriesJob implements ShouldQueue
{
    use Queueable, InteractsWithHttp;

    private const RETRY_DELAY = 60;
    private int $siteId;
    private int $categoryId;
    private string $url;
    private string $jobId;

    public function __construct(int $siteId, int $categoryId, string $url)
    {
        $this->siteId = $siteId;
        $this->categoryId = $categoryId;
        $this->url = $url;
        $this->jobId = uniqid('crawl_list_', true);
    }

    public function handle()
    {
        try {
            $site = DB::table('news_sites')->find($this->siteId);
            if (!$site) {
                Log::error("❌ سایت یافت نشد: {$this->siteId}");
                return;
            }

            $siteName = json_decode($site->name)->en ?? 'Unknown';
            $config = config('crawler.sites.' . $siteName);

            if (!$config) throw new \Exception("کانفیگ یافت نشد برای: $siteName");

            Log::info("🔍 [خزش لیست خبرها]", ['url' => $this->url, 'category_id' => $this->categoryId]);

            $response = $this->sendRequest($this->url, 'get', ['job_id' => $this->jobId]);
            $html = $response->body();

            $crawler = new Crawler($html);
            $links = $crawler->filter($config['category_selectors']['links'])->each(function (Crawler $node) {
                return $this->normalizeUrl($node->attr('href'));
            });

            // حذف لینک‌های نامعتبر و تکراری
            $links = array_unique(array_filter($links, function($link) {
                return !empty($link) && filter_var($link, FILTER_VALIDATE_URL);
            }));

            if (empty($links)) {
                Log::warning("⚠️ هیچ لینکی یافت نشد", ['url' => $this->url]);
                return;
            }

            // بهینه‌سازی: بررسی یکباره تمام لینک‌ها در دیتابیس
            $existingUrls = DB::table('news')
                ->whereIn('source_url', $links)
                ->pluck('source_url')
                ->toArray();

            $newLinks = array_diff($links, $existingUrls);

            foreach ($newLinks as $link) {
                // فیلتر اضافی بر اساس کانفیگ
                if (!empty($config['category_selectors']['filter'])) {
                    if (!str_contains($link, $config['category_selectors']['filter'])) continue;
                }

                // ارسال کانفیگ کامل به جاب بعدی
                CrawlNewsContentJob::dispatch(
                    $siteName,
                    $this->siteId,
                    $this->categoryId,
                    $link,
                    $config['news_selectors']
                );
            }

            Log::info("✅ [نتیجه لیست]", ['total' => count($links), 'new' => count($newLinks)]);

        } catch (\Exception $e) {
            Log::error("❌ [خطا در خزش لیست]", ['url' => $this->url, 'msg' => $e->getMessage()]);
            $this->release(self::RETRY_DELAY);
        }
    }

    private function normalizeUrl(?string $link): string
    {
        if (empty($link)) return '';
        $link = trim($link);

        if (str_starts_with($link, 'http')) return $link;

        // مدیریت لینک‌های نسبی
        $parsed = parse_url($this->url);
        $root = $parsed['scheme'] . '://' . $parsed['host'];

        if (str_starts_with($link, '/')) {
            return $root . $link;
        }

        return $root . '/' . $link;
    }
}
