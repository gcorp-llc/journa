<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    public function __construct(int $newsId, string $siteName, string $url, array $config, ?string $html = null, string $slug = '')
    {
        $this->newsId = $newsId;
        $this->siteName = $siteName;
        $this->url = $url;
        $this->config = $config;
        $this->html = $html;
        $this->slug = $slug ?: 'default-slug-' . $newsId;
    }

    public function handle()
    {
        try {
            $disk = Storage::disk('public');
            $folderPath = self::STORAGE_PATH . '/' . str_replace(' ', '_', $this->siteName);

            if (!$disk->exists($folderPath)) {
                $disk->makeDirectory($folderPath);
            }

            // استفاده از HTML پاس داده شده برای کاهش درخواست HTTP
            $html = $this->html ?? $this->fetchPage();

            $crawler = new Crawler($html);
            $imageUrl = $this->extractImageUrl($crawler);

            if (empty($imageUrl)) {
                Log::warning("هیچ تصویر کاوری برای خبر ID {$this->newsId} پیدا نشد.");
                return;
            }

            $imageUrl = $this->normalizeImageUrl($imageUrl);
            $coverPath = $this->processImage($imageUrl, $folderPath, $disk);

            if ($coverPath) {
                $this->updateNewsCover($coverPath);
                Log::info("تصویر کاور برای خبر ID {$this->newsId} با موفقیت ذخیره شد.");
            }
        } catch (\Exception $e) {
            Log::error("خطا در پردازش تصویر برای خبر ID {$this->newsId}: {$e->getMessage()}", [
                'exception' => $e->getMessage(),
            ]);
            $this->release(self::RETRY_DELAY);
        }
    }

    private function fetchPage(): string
    {
        $response = Http::timeout(self::HTTP_TIMEOUT)->get($this->url);
        if (!$response->ok()) {
            throw new \Exception("خطا در دریافت URL: {$this->url}, وضعیت: {$response->status()}");
        }
        return $response->body();
    }

    private function extractImageUrl(Crawler $crawler): ?string
    {
        if (empty($this->config['news_selectors']['cover'])) {
            Log::warning("سلکتور کاور برای سایت {$this->siteName} تعریف نشده است.");
            return $this->extractFallbackImageUrl($crawler);
        }

        // تلاش برای یافتن اولین تصویر با رزولوشن بالا
        $coverImage = $crawler->filter($this->config['news_selectors']['cover'])->first();

        if ($coverImage->count() === 0) {
            Log::warning("تصویر کاور برای URL {$this->url} پیدا نشد. تلاش برای استفاده از فال‌بک.");
            return $this->extractFallbackImageUrl($crawler);
        }

        $imageUrl = null;

        // بررسی اگر سلکتور به <source> اشاره دارد
        if ($coverImage->nodeName() === 'source') {
            $srcset = $coverImage->attr('srcset');
            if ($srcset) {
                // گرفتن اولین URL از srcset
                $imageUrl = trim(explode(',', $srcset)[0]);
            }
        } else {
            // اگر سلکتور به <img> اشاره دارد
            $imageUrl = $coverImage->attr('src');
        }

        // بررسی اعتبار URL
        if (empty($imageUrl) || str_starts_with($imageUrl, 'data:image/')) {
            Log::warning("URL تصویر نامعتبر است یا Data URL است: {$imageUrl}. تلاش برای استفاده از فال‌بک.");
            return $this->extractFallbackImageUrl($crawler);
        }

        return $imageUrl;
    }

    private function extractFallbackImageUrl(Crawler $crawler): ?string
    {
        // فال‌بک 1: متا تگ og:image
        $metaImage = $crawler->filter('meta[property="og:image"]')->first();
        if ($metaImage->count() > 0) {
            $imageUrl = $metaImage->attr('content');
            if (!empty($imageUrl) && !str_starts_with($imageUrl, 'data:image/')) {
                return $imageUrl;
            }
        }

        // فال‌بک 2: متا تگ جایگزین
        if (!empty($this->config['news_selectors']['cover_alt'])) {
            $altImage = $crawler->filter($this->config['news_selectors']['cover_alt'])->first();
            if ($altImage->count() > 0) {
                $imageUrl = $altImage->attr('content') ?? $altImage->attr('src');
                if (!empty($imageUrl) && !str_starts_with($imageUrl, 'data:image/')) {
                    return $imageUrl;
                }
            }
        }

        // فال‌بک 3: اولین تصویر بزرگ در صفحه
        $firstLargeImg = $crawler->filter('img')->reduce(function (Crawler $node) {
            $src = $node->attr('src');
            // در زمان خزش، width و height ممکن است لود نشده باشند، لذا فیلتر بر اساس تگ‌های img ساده‌تر
            return !empty($src) && !str_starts_with($src, 'data:image/') &&
                (Str::contains($src, ['large', 'medium', 'content', 'uploads']) || (int) $node->attr('width') > self::MIN_IMAGE_DIMENSION);
        })->first();

        if ($firstLargeImg->count() > 0) {
            return $firstLargeImg->attr('src');
        }

        return null;
    }

    private function normalizeImageUrl(string $imageUrl): string
    {
        if (!str_starts_with($imageUrl, 'http')) {
            $baseUrl = parse_url($this->url, PHP_URL_SCHEME) . '://' . parse_url($this->url, PHP_URL_HOST);
            return rtrim($baseUrl, '/') . '/' . ltrim($imageUrl, '/');
        }
        return $imageUrl;
    }

    private function processImage(string $imageUrl, string $folderPath, $disk): ?string
    {
        try {
            $imageResponse = Http::timeout(self::HTTP_TIMEOUT)->get($imageUrl);
            if (!$imageResponse->ok()) {
                Log::warning("خطا در دریافت تصویر {$imageUrl}: وضعیت {$imageResponse->status()}");
                return null;
            }

            $imageContent = $imageResponse->body();
            $imageInfo = @getimagesizefromstring($imageContent);

            if ($imageInfo === false) {
                Log::warning("تصویر {$imageUrl} معتبر نیست.");
                return null;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];

            if ($width >= self::MIN_IMAGE_DIMENSION && $height >= self::MIN_IMAGE_DIMENSION) {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($imageContent);

                // تغییر سایز با حفظ نسبت
                $image->scale(width: self::IMAGE_WIDTH);

                // ذخیره به فرمت WebP برای افزایش سرعت سایت
                $imageContent = $image->toWebp(self::IMAGE_QUALITY);

                // نام فایل بر اساس slug
                $imageName = Str::slug($this->slug) . '-' . uniqid() . '.webp';
                $imagePath = $folderPath . '/' . $imageName;

                $disk->put($imagePath, $imageContent);
                return $imagePath;
            } else {
                Log::warning("تصویر {$imageUrl} ابعاد کافی ندارد: {$width}x{$height}");
                return null;
            }
        } catch (\Exception $e) {
            Log::warning("خطا در پردازش تصویر {$imageUrl}: {$e->getMessage()}");
            return null;
        }
    }

    private function updateNewsCover(string $coverPath): void
    {
        \Illuminate\Support\Facades\DB::table('news')
            ->where('id', $this->newsId)
            ->update(['cover' => $coverPath, 'updated_at' => now()]);
    }
}
