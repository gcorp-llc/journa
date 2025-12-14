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
    private string $jobId;

    public function __construct(?int $siteId = null)
    {
        $this->siteId = $siteId;
        $this->jobId = uniqid('crawl_cat_', true);
    }

    public function handle()
    {
        $startTime = microtime(true);

        try {
            Log::info("🔍 [شروع خزش دسته‌بندی‌ها] Job ID: {$this->jobId}", [
                'site_id' => $this->siteId,
                'environment' => app()->environment(),
            ]);

            // ساخت کوئری پایه
            $query = DB::table('news_site_categories')
//                ->where('is_active', true) // فرض بر این است که فیلد فعال/غیرفعال دارید
                ->where(function ($query) {
                    $query->where('last_crawled', '<', now()->subHours(1))
                        ->orWhereNull('last_crawled');
                })
                ->when($this->siteId, fn ($q) => $q->where('news_site_id', $this->siteId));

            $totalCategories = $query->count();

            if ($totalCategories === 0) {
                Log::info("⚠️ [هیچ دسته‌بندی برای خزش یافت نشد]", ['job_id' => $this->jobId]);
                return;
            }

            Log::info("📊 [تعداد دسته‌بندی‌های واجد شرایط]: {$totalCategories}");

            // استفاده از chunk برای جلوگیری از پر شدن حافظه در تعداد بالا
            $query->orderBy('last_crawled', 'asc')
                ->chunk(50, function ($categories) {
                    $this->processBatch($categories);
                });

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("✨ [تکمیل جاب CrawlCategoryLinksJob]", ['time_ms' => $executionTime]);

        } catch (\Exception $e) {
            Log::error("💥 [خطای بحرانی CrawlCategoryLinksJob]", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }

    private function processBatch($categories)
    {
        $categoriesToUpdate = [];
        $dispatchedCount = 0;

        foreach ($categories as $category) {
            try {
                // تاخیر تصادفی برای جلوگیری از بلاک شدن توسط سرور مقصد
                $delaySeconds = app()->environment('production') ? rand(2, 15) : 0;

                CrawlNewsCategoriesJob::dispatch(
                    $category->news_site_id,
                    $category->category_id ?? $category->id, // هندل کردن نام‌گذاری متفاوت احتمالی
                    $category->category_url
                )->delay(now()->addSeconds($delaySeconds));

                $categoriesToUpdate[] = $category->id;
                $dispatchedCount++;

            } catch (\Exception $e) {
                Log::error("❌ [خطا در ارسال جاب]", ['category_id' => $category->id, 'msg' => $e->getMessage()]);
            }
        }

        // آپدیت دسته‌ای زمان خزش
        if (!empty($categoriesToUpdate)) {
            DB::table('news_site_categories')
                ->whereIn('id', $categoriesToUpdate)
                ->update(['last_crawled' => now()]);

            Log::info("🔄 [آپدیت وضعیت دسته‌بندی‌ها]", ['count' => count($categoriesToUpdate)]);
        }
    }
}
