<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrawlCategoryLinksJob implements ShouldQueue
{
    use Queueable;

    private ?int $siteId;

    public function __construct(?int $siteId = null)
    {
        $this->siteId = $siteId;
    }

    public function handle()
    {
        try {
            $query = DB::table('news_site_categories')
                ->where('last_crawled', '<', now()->subHours(1))
                ->orWhereNull('last_crawled');

            if ($this->siteId) {
                $query->where('news_site_id', $this->siteId);
            }

            // در حالت تست فقط اولین دسته‌بندی را انتخاب کن
            if (app()->environment('testing')) {
                $categories = $query->take(1)->get();
            } else {
                $categories = $query->get();
            }

            if ($categories->isEmpty()) {
                Log::info('No categories to crawl.', ['site_id' => $this->siteId]);
                return;
            }

            foreach ($categories as $category) {
                $delaySeconds = app()->environment('testing') ? 0 : rand(1, 10); // بدون تأخیر در تست
                CrawlNewsCategoriesJob::dispatch(
                    $category->news_site_id,
                    $category->category_id,
                    $category->category_url
                )->delay(now()->addSeconds($delaySeconds));

                DB::table('news_site_categories')->where('id', $category->id)->update(['last_crawled' => now()]);
            }
        } catch (\Exception $e) {
            Log::error("Error in CrawlCategoryLinksJob: {$e->getMessage()}", [
                'exception' => $e->getTraceAsString(),
                'site_id' => $this->siteId
            ]);
            throw $e; // برای اطلاع از خطاها در تست
        }
    }
}
