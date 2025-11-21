<?php

namespace App\Jobs;

use App\Traits\InteractsWithHttp; // ✅ استفاده از Trait
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
        $this->jobId = uniqid('crawl_cat_', true);
    }

    public function handle()
    {
        try {
            // دریافت نام سایت
            $site = DB::table('news_sites')->find($this->siteId);
            if (!$site) throw new \Exception("سایت یافت نشد");

            $siteName = json_decode($site->name)->en ?? 'Unknown';
            $config = config('crawler.sites.' . $siteName);

            if (!$config) throw new \Exception("کانفیگ یافت نشد برای: $siteName");

            Log::info("🔍 [شروع خزش دسته]", ['url' => $this->url, 'site' => $siteName]);

            // دریافت صفحه با Trait
            $response = $this->sendRequest($this->url, 'get', ['job_id' => $this->jobId]);
            $html = $response->body();

            $crawler = new Crawler($html);

            // استخراج لینک‌ها
            $links = $crawler->filter($config['category_selectors']['links'])->each(function (Crawler $node) {
                return $this->normalizeUrl($node->attr('href'));
            });

            // حذف تکراری‌ها و خالی‌ها
            $links = array_unique(array_filter($links));

            // بررسی لینک‌های قبلاً کرول شده
            $existing = DB::table('news')->whereIn('source_url', $links)->pluck('source_url')->toArray();
            $newLinks = array_diff($links, $existing);

            foreach ($newLinks as $link) {
                // فیلتر اضافی (اگر در کانفیگ باشد)
                if (!empty($config['category_selectors']['filter'])) {
                    if (!str_contains($link, $config['category_selectors']['filter'])) continue;
                }

                CrawlNewsContentJob::dispatch(
                    $siteName,
                    $this->siteId,
                    $this->categoryId,
                    $link,
                    $config['news_selectors']
                );
            }

            Log::info("✅ [پایان خزش دسته]", ['کل لینک‌ها' => count($links), 'جدید' => count($newLinks)]);

        } catch (\Exception $e) {
            Log::error("❌ [خطا دسته بندی]", ['url' => $this->url, 'msg' => $e->getMessage()]);
            $this->release(self::RETRY_DELAY);
        }
    }

    private function normalizeUrl(?string $link): string
    {
        if (empty($link)) return '';
        if (str_starts_with($link, 'http')) return $link;

        $parsed = parse_url($this->url);
        $root = $parsed['scheme'] . '://' . $parsed['host'];
        return $root . '/' . ltrim($link, '/');
    }
}
