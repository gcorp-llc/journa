<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
                'timestamp' => now()->toDateTimeString(),
                'environment' => app()->environment(),
            ]);

            // 1. اولویت‌بندی و محدود کردن کوئری
            $query = DB::table('news_site_categories')
                ->where(function ($query) {
                    $query->where('last_crawled', '<', now()->subHours(1))
                        ->orWhereNull('last_crawled');
                })
                ->when($this->siteId, fn ($q) => $q->where('news_site_id', $this->siteId));

            $totalCategories = $query->count();

            Log::info("📊 [شمارش دسته‌بندی‌ها] تعداد کل: {$totalCategories}", [
                'job_id' => $this->jobId,
                'site_id' => $this->siteId,
            ]);

            // انتخاب همه دسته‌بندی‌های واجد شرایط بدون محدودیت batch
            $categories = $query->orderBy('last_crawled', 'asc')
                ->get();

            if ($categories->isEmpty()) {
                Log::warning("⚠️  [هیچ دسته‌بندی برای خزش وجود ندارد]", [
                    'job_id' => $this->jobId,
                    'site_id' => $this->siteId,
                    'query_count' => $totalCategories,
                ]);
                return;
            }

            Log::info("✅ [دسته‌بندی‌های برای خزش انتخاب شد] تعداد: {$categories->count()}", [
                'job_id' => $this->jobId,
                'categories' => $categories->pluck('category_id')->toArray(),
            ]);

            $categoriesToUpdate = [];
            $dispatchedCount = 0;
            $errorCount = 0;

            foreach ($categories as $category) {
                try {
                    $delaySeconds = app()->environment('testing') ? 0 : rand(1, 10);

                    Log::debug("📤 [ارسال جاب خزش برای دسته‌بندی]", [
                        'job_id' => $this->jobId,
                        'category_id' => $category->category_id,
                        'site_id' => $category->news_site_id,
                        'url' => $category->category_url,
                        'delay_seconds' => $delaySeconds,
                    ]);

                    // ارسال جاب خزش دسته‌بندی
                    CrawlNewsCategoriesJob::dispatch(
                        $category->news_site_id,
                        $category->category_id,
                        $category->category_url
                    )->delay(now()->addSeconds($delaySeconds));

                    $categoriesToUpdate[] = $category->id;
                    $dispatchedCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("❌ [خطا در ارسال جاب برای دسته‌بندی]", [
                        'job_id' => $this->jobId,
                        'category_id' => $category->category_id,
                        'category_url' => $category->category_url,
                        'error_message' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
            }

            Log::info("📨 [خلاصه ارسال جاب‌ها]", [
                'job_id' => $this->jobId,
                'total_processed' => $categories->count(),
                'successfully_dispatched' => $dispatchedCount,
                'failed_count' => $errorCount,
            ]);

            // 2. به‌روزرسانی دسته‌ای (Bulk Update)
            if (!empty($categoriesToUpdate)) {
                try {
                    $updatedCount = DB::table('news_site_categories')
                        ->whereIn('id', $categoriesToUpdate)
                        ->update(['last_crawled' => now()]);

                    Log::info("🔄 [به‌روزرسانی last_crawled در دیتابیس]", [
                        'job_id' => $this->jobId,
                        'updated_count' => $updatedCount,
                        'expected_count' => count($categoriesToUpdate),
                    ]);

                    if ($updatedCount !== count($categoriesToUpdate)) {
                        Log::warning("⚠️  [عدم تطابق در تعداد رکوردهای به‌روزرسانی‌شده]", [
                            'job_id' => $this->jobId,
                            'expected' => count($categoriesToUpdate),
                            'updated' => $updatedCount,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("❌ [خطا در به‌روزرسانی دیتابیس]", [
                        'job_id' => $this->jobId,
                        'error_message' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                    throw $e;
                }
            }

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info("✨ [تکمیل موفقیت‌آمیز CrawlCategoryLinksJob]", [
                'job_id' => $this->jobId,
                'site_id' => $this->siteId,
                'execution_time_ms' => $executionTime,
                'total_categories_checked' => $totalCategories,
                'categories_processed' => $categories->count(),
                'categories_dispatched' => $dispatchedCount,
                'categories_failed' => $errorCount,
                'timestamp' => now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error("💥 [خطای حرجِ در CrawlCategoryLinksJob]", [
                'job_id' => $this->jobId,
                'site_id' => $this->siteId,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => class_basename($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'stack_trace' => explode("\n", $e->getTraceAsString()),
                'execution_time_ms' => $executionTime,
                'timestamp' => now()->toDateTimeString(),
            ]);
            throw $e;
        }
    }
}
