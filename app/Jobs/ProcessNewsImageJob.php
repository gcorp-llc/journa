<?php

namespace App\Jobs;

use App\Traits\InteractsWithHttp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessNewsImageJob implements ShouldQueue
{
    use Queueable, InteractsWithHttp;

    private const MIN_IMAGE_DIMENSION = 300;
    private const STORAGE_PATH = 'content_images'; // تغییر مسیر ذخیره به یک مقدار ثابت مناسب
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
            // 1. پیدا کردن URL تصویر
            $imageUrl = $this->findImageUrl();

            if (!$imageUrl) {
                Log::warning("⚠️ [تصویر یافت نشد]", ['news_id' => $this->newsId]);
                return;
            }

            // 2. دانلود تصویر با Trait
            $response = $this->sendRequest($imageUrl, 'get', ['job_id' => $this->jobId]);
            $imageContent = $response->body();

            if (strlen($imageContent) < 1000) {
                throw new \Exception("فایل تصویر ناقص یا خیلی کوچک است");
            }

            // 3. پردازش با Intervention Image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageContent);

            // تغییر سایز و تبدیل به WebP
            $image->scale(width: self::IMAGE_WIDTH);
            $encoded = $image->toWebp(quality: self::IMAGE_QUALITY);

            $path = self::STORAGE_PATH . '/' . date('Y-m') . '/' . $this->slug . '-' . uniqid() . '.webp';
            // استفاده از Storage::disk('public') طبق قواعد لاراول
            Storage::disk('public')->put($path, $encoded);

            // 4. ذخیره در دیتابیس
            DB::table('news')->where('id', $this->newsId)->update(['cover' => $path]);

            Log::info("✅ [تصویر ذخیره شد]", ['path' => $path]);

        } catch (\Exception $e) {
            Log::error("❌ [خطای تصویر]", ['news_id' => $this->newsId, 'msg' => $e->getMessage()]);
            // نباید جاب را ری‌استارت کنیم اگر مشکل از پردازش تصویر است
        }
    }

    /**
     * پیدا کردن URL تصویر با اولویت‌دهی به JSON-LD و متاتگ‌ها
     * @return string|null
     */
    private function findImageUrl(): ?string
    {
        // اولویت 1: تصویر استخراج شده از JSON-LD در جاب قبلی
        $jsonLdImage = $this->config['news_selectors']['json_ld_image'] ?? null;
        if (is_string($jsonLdImage) && !empty($jsonLdImage)) {
            return $jsonLdImage;
        }

        // اولویت 2: متاتگ og:image (با استفاده از HTML پاس داده شده)
        $crawler = new Crawler($this->html ?? '');
        try {
            if ($crawler->filter('meta[property="og:image"]')->count() > 0) {
                $url = $crawler->filter('meta[property="og:image"]')->attr('content');
                if (filter_var($url, FILTER_VALIDATE_URL)) return $url;
            }
        } catch (\Exception $e) {}

        // اولویت 3: سلکتورهای CSS تعریف شده در کانفیگ
        $selectors = ['cover_carousel', 'cover'];
        foreach ($selectors as $key) {
            if (!empty($this->config['news_selectors'][$key])) {
                try {
                    $sel = $this->config['news_selectors'][$key];
                    $node = $crawler->filter($sel);
                    if ($node->count() > 0) {
                        $url = $node->attr('src') ?? $node->attr('data-src');
                        if (filter_var($url, FILTER_VALIDATE_URL)) return $url;
                    }
                } catch (\Exception $e) {}
            }
        }

        return null;
    }
}
