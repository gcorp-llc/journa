<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class ProcessNewsImageJob implements ShouldQueue
{
    use Queueable;

    private const HTTP_TIMEOUT = 10;
    private const MIN_IMAGE_DIMENSION = 300;
    private const STORAGE_PATH = 'content_images';
    private const RETRY_DELAY = 60;
    private const IMAGE_QUALITY = 80;
    private const IMAGE_WIDTH = 1200;

    private int $newsId;
    private string $siteName;
    private string $url;
    private array $config;
    private ?string $html;
    private string $slug;
    private string $jobId;
    private float $startTime;

    public function __construct(int $newsId, string $siteName, string $url, array $config, ?string $html = null, string $slug = '')
    {
        $this->newsId = $newsId;
        $this->siteName = $siteName;
        $this->url = $url;
        $this->config = $config;
        $this->html = $html;
        $this->slug = $slug ?: 'default-slug-' . $newsId;
        $this->jobId = uniqid('process_img_', true);
        $this->startTime = microtime(true);
    }

    public function handle()
    {
        try {
            Log::info("🖼️  [شروع پردازش تصویر خبر]", [
                'job_id' => $this->jobId,
                'news_id' => $this->newsId,
                'site_name' => $this->siteName,
                'url' => $this->url,
                'slug' => $this->slug,
                'timestamp' => now()->toDateTimeString(),
            ]);

            // آماده‌سازی دایرکتوری
            $disk = Storage::disk('public');
            $folderPath = self::STORAGE_PATH . '/' . str_replace(' ', '_', $this->siteName);

            if (!$disk->exists($folderPath)) {
                $disk->makeDirectory($folderPath);
                Log::debug("📁 [دایرکتوری ایجاد شد]", [
                    'job_id' => $this->jobId,
                    'folder_path' => $folderPath,
                ]);
            } else {
                Log::debug("✅ [دایرکتوری موجود است]", [
                    'job_id' => $this->jobId,
                    'folder_path' => $folderPath,
                ]);
            }

            // دریافت HTML
            $html = $this->html ?? $this->fetchPage();
            $crawler = new Crawler($html);

            Log::debug("📄 [HTML بارگذاری شد]", [
                'job_id' => $this->jobId,
                'html_size_kb' => round(strlen($html) / 1024, 2),
                'source' => $this->html ? 'from_constructor' : 'fetched',
            ]);

            // استخراج URL تصویر
            $imageUrl = $this->extractImageUrl($crawler);

            if (empty($imageUrl)) {
                Log::warning("⚠️  [هیچ تصویر کاوری برای خبر پیدا نشد]", [
                    'job_id' => $this->jobId,
                    'news_id' => $this->newsId,
                    'url' => $this->url,
                ]);
                return;
            }

            Log::info("🔗 [URL تصویر استخراج شد]", [
                'job_id' => $this->jobId,
                'image_url' => $imageUrl,
                'url_length' => strlen($imageUrl),
            ]);

            // نرمال‌سازی URL
            $normalizedUrl = $this->normalizeImageUrl($imageUrl);

            Log::debug("🔄 [URL تصویر نرمال‌سازی شد]", [
                'job_id' => $this->jobId,
                'original_url' => $imageUrl,
                'normalized_url' => $normalizedUrl,
                'changed' => $imageUrl !== $normalizedUrl,
            ]);

            // پردازش تصویر
            $coverPath = $this->processImage($normalizedUrl, $folderPath, $disk);

            if ($coverPath) {
                Log::info("💾 [تصویر ذخیره شد]", [
                    'job_id' => $this->jobId,
                    'news_id' => $this->newsId,
                    'cover_path' => $coverPath,
                ]);

                $this->updateNewsCover($coverPath);

                Log::info("🎉 [دیتابیس به‌روزرسانی شد]", [
                    'job_id' => $this->jobId,
                    'news_id' => $this->newsId,
                    'cover_path' => $coverPath,
                ]);
            } else {
                Log::warning("⚠️  [پردازش تصویر ناموفق]", [
                    'job_id' => $this->jobId,
                    'news_id' => $this->newsId,
                    'image_url' => $normalizedUrl,
                ]);
                return;
            }

            $executionTime = round((microtime(true) - $this->startTime) * 1000, 2);

            Log::info("✨ [تکمیل موفقیت‌آمیز ProcessNewsImageJob]", [
                'job_id' => $this->jobId,
                'news_id' => $this->newsId,
                'site_name' => $this->siteName,
                'image_url' => $imageUrl,
                'cover_path' => $coverPath,
                'execution_time_ms' => $executionTime,
                'timestamp' => now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    private function fetchPage(): string
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
                    "خطا در دریافت صفحه. وضعیت HTTP: {$response->status()} ({$response->reason()})"
                );
            }

            return $response->body();

        } catch (\Exception $e) {
            Log::error("❌ [خطا در دریافت صفحه]", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function extractImageUrl(Crawler $crawler): ?string
    {
        try {
            Log::debug("🔎 [شروع استخراج URL تصویر]", [
                'job_id' => $this->jobId,
                'strategies' => ['cover_carousel', 'cover', 'fallback'],
            ]);

            // استراتژی 1: cover_carousel
            $carouselSelector = $this->config['news_selectors']['cover_carousel'] ?? null;
            if ($carouselSelector) {
                Log::debug("🎠 [استراتژی 1: بررسی cover_carousel]", [
                    'job_id' => $this->jobId,
                    'selector' => $carouselSelector,
                ]);

                try {
                    $carouselImage = $crawler->filter($carouselSelector)->first();
                    if ($carouselImage->count() > 0) {
                        $src = $this->getImageSrcFromNode($carouselImage);
                        if ($src) {
                            Log::info("✅ [تصویر از cover_carousel استخراج شد]", [
                                'job_id' => $this->jobId,
                                'image_url' => $src,
                                'source' => 'cover_carousel',
                            ]);
                            return $src;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("⚠️  [خطا در پردازش cover_carousel]", [
                        'job_id' => $this->jobId,
                        'selector' => $carouselSelector,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::debug("ℹ️  [سلکتور cover_carousel تعریف نشده]", [
                    'job_id' => $this->jobId,
                ]);
            }

            // استراتژی 2: cover
            $coverSelector = $this->config['news_selectors']['cover'] ?? null;
            if ($coverSelector) {
                Log::debug("🖼️  [استراتژی 2: بررسی cover]", [
                    'job_id' => $this->jobId,
                    'selector' => $coverSelector,
                ]);

                try {
                    $coverImage = $crawler->filter($coverSelector)->first();
                    if ($coverImage->count() > 0) {
                        $src = $this->getImageSrcFromNode($coverImage);
                        if ($src) {
                            Log::info("✅ [تصویر از cover استخراج شد]", [
                                'job_id' => $this->jobId,
                                'image_url' => $src,
                                'source' => 'cover',
                            ]);
                            return $src;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("⚠️  [خطا در پردازش cover]", [
                        'job_id' => $this->jobId,
                        'selector' => $coverSelector,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::debug("ℹ️  [سلکتور cover تعریف نشده]", [
                    'job_id' => $this->jobId,
                ]);
            }

            // استراتژی 3: fallback
            Log::debug("🎣 [استراتژی 3: بررسی fallback]", [
                'job_id' => $this->jobId,
            ]);

            return $this->extractFallbackImageUrl($crawler);

        } catch (\Exception $e) {
            Log::error("❌ [خطا در استخراج URL تصویر]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function getImageSrcFromNode(Crawler $node): ?string
    {
        try {
            $nodeName = $node->nodeName();
            Log::debug("🔍 [تجزیه نود تصویر]", [
                'job_id' => $this->jobId,
                'node_type' => $nodeName,
            ]);

            $src = null;

            if ($nodeName === 'source') {
                $src = $node->attr('srcset');
                Log::debug("📋 [استخراج srcset از source]", [
                    'job_id' => $this->jobId,
                    'srcset' => substr($src, 0, 100),
                ]);
            } elseif ($nodeName === 'img') {
                $dataSrc = $node->attr('data-src');
                $imgSrc = $node->attr('src');
                $src = $dataSrc ?? $imgSrc;

                Log::debug("📸 [استخراج src از img]", [
                    'job_id' => $this->jobId,
                    'has_data_src' => !empty($dataSrc),
                    'has_src' => !empty($imgSrc),
                    'used' => !empty($dataSrc) ? 'data-src' : 'src',
                ]);
            }

            if (empty($src)) {
                Log::debug("⛔ [هیچ src پیدا نشد]", [
                    'job_id' => $this->jobId,
                    'node_type' => $nodeName,
                ]);
                return null;
            }

            // پاکسازی srcset
            $firstPart = trim(explode(',', $src)[0]);
            $url = trim(explode(' ', $firstPart)[0]);

            Log::debug("🧹 [پاکسازی URL]", [
                'job_id' => $this->jobId,
                'original' => substr($src, 0, 100),
                'cleaned' => $url,
            ]);

            if (empty($url) || str_starts_with($url, 'data:image/')) {
                Log::debug("⛔ [URL خالی یا data URI]", [
                    'job_id' => $this->jobId,
                    'url' => $url,
                ]);
                return null;
            }

            return $url;

        } catch (\Exception $e) {
            Log::warning("⚠️  [خطا در تجزیه نود تصویر]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function extractFallbackImageUrl(Crawler $crawler): ?string
    {
        try {
            Log::debug("🎣 [شروع استخراج تصویر fallback]", [
                'job_id' => $this->jobId,
                'methods' => ['og:image', 'cover_alt', 'first_large_img'],
            ]);

            // روش 1: og:image
            try {
                $metaImage = $crawler->filter('meta[property="og:image"]')->first();
                if ($metaImage->count() > 0) {
                    $imageUrl = $metaImage->attr('content');
                    if (!empty($imageUrl) && !str_starts_with($imageUrl, 'data:image/')) {
                        Log::info("✅ [تصویر از og:image پیدا شد]", [
                            'job_id' => $this->jobId,
                            'image_url' => $imageUrl,
                            'source' => 'og:image',
                        ]);
                        return $imageUrl;
                    }
                }
            } catch (\Exception $e) {
                Log::debug("⚠️  [خطا در og:image]", [
                    'job_id' => $this->jobId,
                    'error' => $e->getMessage(),
                ]);
            }

            // روش 2: cover_alt
            if (!empty($this->config['news_selectors']['cover_alt'])) {
                try {
                    $altImage = $crawler->filter($this->config['news_selectors']['cover_alt'])->first();
                    if ($altImage->count() > 0) {
                        $imageUrl = $altImage->attr('content') ?? $altImage->attr('src');
                        if (!empty($imageUrl) && !str_starts_with($imageUrl, 'data:image/')) {
                            Log::info("✅ [تصویر از cover_alt پیدا شد]", [
                                'job_id' => $this->jobId,
                                'image_url' => $imageUrl,
                                'source' => 'cover_alt',
                            ]);
                            return $imageUrl;
                        }
                    }
                } catch (\Exception $e) {
                    Log::debug("⚠️  [خطا در cover_alt]", [
                        'job_id' => $this->jobId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // روش 3: اولین تصویر بزرگ
            try {
                Log::debug("📸 [جستجو برای اولین تصویر بزرگ]", [
                    'job_id' => $this->jobId,
                    'min_dimension' => self::MIN_IMAGE_DIMENSION,
                ]);

                $firstLargeImg = $crawler->filter('img')->reduce(function (Crawler $node) {
                    $src = $node->attr('src') ?? $node->attr('data-src');
                    $width = (int) $node->attr('width');
                    $hasContentKeywords = Str::contains($src, ['large', 'medium', 'content', 'uploads']);
                    $isLargeDimension = $width > self::MIN_IMAGE_DIMENSION;

                    return !empty($src) &&
                        !str_starts_with($src, 'data:image/') &&
                        ($hasContentKeywords || $isLargeDimension);
                })->first();

                if ($firstLargeImg->count() > 0) {
                    $imageUrl = $firstLargeImg->attr('src') ?? $firstLargeImg->attr('data-src');
                    Log::info("✅ [اولین تصویر بزرگ پیدا شد]", [
                        'job_id' => $this->jobId,
                        'image_url' => $imageUrl,
                        'source' => 'first_large_img',
                        'width' => (int) $firstLargeImg->attr('width'),
                    ]);
                    return $imageUrl;
                }
            } catch (\Exception $e) {
                Log::debug("⚠️  [خطا در جستجوی اولین تصویر بزرگ]", [
                    'job_id' => $this->jobId,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::warning("⚠️  [هیچ تصویر fallback پیدا نشد]", [
                'job_id' => $this->jobId,
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error("❌ [خطا در استخراج تصویر fallback]", [
                'job_id' => $this->jobId,
                'error_message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function normalizeImageUrl(string $imageUrl): string
    {
        try {
            if (str_starts_with($imageUrl, 'http')) {
                Log::debug("✅ [URL تصویر از قبل مطلق است]", [
                    'job_id' => $this->jobId,
                    'url' => $imageUrl,
                ]);
                return $imageUrl;
            }

            $scheme = parse_url($this->url, PHP_URL_SCHEME);
            $host = parse_url($this->url, PHP_URL_HOST);

            if (!$scheme || !$host) {
                Log::warning("⚠️  [نتوانست URL پایه را تجزیه کند]", [
                    'job_id' => $this->jobId,
                    'base_url' => $this->url,
                    'scheme' => $scheme,
                    'host' => $host,
                ]);
                return $imageUrl;
            }

            $baseUrl = $scheme . '://' . $host;
            $normalizedUrl = rtrim($baseUrl, '/') . '/' . ltrim($imageUrl, '/');

            Log::debug("🔄 [URL نسبی به مطلق تبدیل شد]", [
                'job_id' => $this->jobId,
                'relative_url' => $imageUrl,
                'absolute_url' => $normalizedUrl,
                'base_url' => $baseUrl,
            ]);

            return $normalizedUrl;

        } catch (\Exception $e) {
            Log::error("❌ [خطا در نرمال‌سازی URL]", [
                'job_id' => $this->jobId,
                'image_url' => $imageUrl,
                'error_message' => $e->getMessage(),
            ]);
            return $imageUrl;
        }
    }

    private function processImage(string $imageUrl, string $folderPath, $disk): ?string
    {
        try {
            Log::debug("📥 [شروع دریافت تصویر از URL]", [
                'job_id' => $this->jobId,
                'image_url' => $imageUrl,
                'timeout_seconds' => self::HTTP_TIMEOUT,
            ]);

            $imageResponse = Http::timeout(self::HTTP_TIMEOUT)->get($imageUrl);

            if (!$imageResponse->ok()) {
                Log::warning("⚠️  [دریافت تصویر ناموفق]", [
                    'job_id' => $this->jobId,
                    'image_url' => $imageUrl,
                    'status_code' => $imageResponse->status(),
                    'status_reason' => $imageResponse->reason(),
                ]);
                return null;
            }

            $imageContent = $imageResponse->body();
            Log::debug("✅ [تصویر دریافت شد]", [
                'job_id' => $this->jobId,
                'image_size_kb' => round(strlen($imageContent) / 1024, 2),
            ]);

            // بررسی صحت تصویر
            $imageInfo = @getimagesizefromstring($imageContent);
            if ($imageInfo === false) {
                Log::warning("⚠️  [تصویر معتبر نیست]", [
                    'job_id' => $this->jobId,
                    'image_url' => $imageUrl,
                ]);
                return null;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mimeType = $imageInfo['mime'] ?? 'نامعلوم';

            Log::debug("📊 [اطلاعات تصویر]", [
                'job_id' => $this->jobId,
                'width' => $width,
                'height' => $height,
                'mime_type' => $mimeType,
                'aspect_ratio' => round($width / $height, 2),
            ]);

            // بررسی ابعاد
            if ($width < self::MIN_IMAGE_DIMENSION || $height < self::MIN_IMAGE_DIMENSION) {
                Log::warning("⚠️  [ابعاد تصویر کافی نیست]", [
                    'job_id' => $this->jobId,
                    'image_url' => $imageUrl,
                    'width' => $width,
                    'height' => $height,
                    'minimum_required' => self::MIN_IMAGE_DIMENSION,
                ]);
                return null;
            }

            // تبدیل به webp
            Log::debug("🎨 [شروع پردازش تصویر]", [
                'job_id' => $this->jobId,
                'target_width' => self::IMAGE_WIDTH,
                'quality' => self::IMAGE_QUALITY,
                'format' => 'webp',
            ]);

            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageContent);
            $image->scale(width: self::IMAGE_WIDTH);
            $processedContent = $image->toWebp(self::IMAGE_QUALITY);

            Log::debug("✅ [تصویر به webp تبدیل شد]", [
                'job_id' => $this->jobId,
                'processed_size_kb' => round(strlen($processedContent) / 1024, 2),
                'compression_ratio' => round((1 - strlen($processedContent) / strlen($imageContent)) * 100, 2) . '%',
            ]);

            // ذخیره تصویر
            $imageName = Str::slug($this->slug) . '-' . uniqid() . '.webp';
            $imagePath = $folderPath . '/' . $imageName;

            $disk->put($imagePath, $processedContent);

            Log::info("✅ [تصویر در دیسک ذخیره شد]", [
                'job_id' => $this->jobId,
                'image_path' => $imagePath,
                'file_name' => $imageName,
                'folder_path' => $folderPath,
            ]);

            return $imagePath;

        } catch (\Exception $e) {
            Log::error("❌ [خطا در پردازش تصویر]", [
                'job_id' => $this->jobId,
                'image_url' => $imageUrl,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
            ]);
            return null;
        }
    }

    private function updateNewsCover(string $coverPath): void
    {
        try {
            $updated = DB::table('news')
                ->where('id', $this->newsId)
                ->update([
                    'cover' => $coverPath,
                    'updated_at' => now()
                ]);

            Log::debug("💾 [نتیجه به‌روزرسانی دیتابیس]", [
                'job_id' => $this->jobId,
                'news_id' => $this->newsId,
                'affected_rows' => $updated,
                'cover_path' => $coverPath,
            ]);

            if ($updated === 0) {
                Log::warning("⚠️  [هیچ ردیفی برای به‌روزرسانی یافت نشد]", [
                    'job_id' => $this->jobId,
                    'news_id' => $this->newsId,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("❌ [خطا در به‌روزرسانی دیتابیس]", [
                'job_id' => $this->jobId,
                'news_id' => $this->newsId,
                'cover_path' => $coverPath,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function handleError(\Exception $e): void
    {
        $executionTime = round((microtime(true) - $this->startTime) * 1000, 2);

        Log::error("💥 [خطا در ProcessNewsImageJob]", [
            'job_id' => $this->jobId,
            'news_id' => $this->newsId,
            'site_name' => $this->siteName,
            'url' => $this->url,
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'error_class' => class_basename($e),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'execution_time_ms' => $executionTime,
            'timestamp' => now()->toDateTimeString(),
        ]);

        $this->release(self::RETRY_DELAY);
    }
}
