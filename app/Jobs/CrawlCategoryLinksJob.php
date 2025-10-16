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
            // 1. اولویت‌بندی و محدود کردن کوئری
            $query = DB::table('news_site_categories')
                ->where(function ($query) {
                    $query->where('last_crawled', '<', now()->subHours(1))
                        ->orWhereNull('last_crawled');
                })
                ->when($this->siteId, fn ($q) => $q->where('news_site_id', $this->siteId));

            // انتخاب محدود و بر اساس قدیمی‌ترین خزش (افزایش سرعت و توزیع بار)
            $categories = $query->orderBy('last_crawled', 'asc')
                ->take(app()->environment('testing') ? 1 : 20) // محدودیت برای تست و تولید
                ->get();

            if ($categories->isEmpty()) {
                Log::info('No categories to crawl.', ['site_id' => $this->siteId]);
                return;
            }

            $categoriesToUpdate = [];

            foreach ($categories as $category) {
                $delaySeconds = app()->environment('testing') ? 0 : rand(1, 10);

                // ارسال جاب خزش دسته‌بندی
                CrawlNewsCategoriesJob::dispatch(
                    $category->news_site_id,
                    $category->category_id,
                    $category->category_url
                )->delay(now()->addSeconds($delaySeconds));

                $categoriesToUpdate[] = $category->id;
            }

            // 2. به‌روزرسانی دسته‌ای (Bulk Update) برای افزایش سرعت دیتابیس
            if (!empty($categoriesToUpdate)) {
                DB::table('news_site_categories')
                    ->whereIn('id', $categoriesToUpdate)
                    ->update(['last_crawled' => now()]);
            }

        } catch (\Exception $e) {
            Log::error("Error in CrawlCategoryLinksJob: {$e->getMessage()}", [
                'exception' => $e->getTraceAsString(),
                'site_id' => $this->siteId
            ]);
            throw $e;
        }
    }
}
