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

    private const STORAGE_PATH = 'content_images';

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
        $this->slug = $slug;
    }

    public function handle()
    {
        try {
            // 1. پیدا کردن لینک تصویر با لاجیک اصلاح شده
            $imageUrl = $this->findImageUrl();

            if (!$imageUrl) {
                Log::warning("⚠️ [تصویر یافت نشد]", ['news_id' => $this->newsId, 'url' => $this->url]);
                return;
            }

            // 2. دانلود تصویر
            $response = $this->sendRequest($imageUrl, 'get');
            $imageContent = $response->body();

            // بررسی صحت فایل دریافتی
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageContent);

            if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/gif'])) {
                Log::warning("⚠️ فرمت نامعتبر تصویر: $mimeType", ['url' => $imageUrl]);
                return;
            }

            // 3. پردازش تصویر
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageContent);

            // ریسایز اگر خیلی بزرگ بود
            if ($image->width() > 1200) {
                $image->scaleDown(width: 1200);
            }

            $encoded = $image->toWebp(quality: 80);

            // 4. ساخت مسیر پوشه بر اساس تاریخ روز (Y-m-d)
            // مثال: content_images/2025-09-16/my-slug-123.webp
            $dateFolder = now()->format('Y-m-d');
            $filename = $this->slug . '-' . uniqid() . '.webp';
            $path = self::STORAGE_PATH . '/' . $dateFolder . '/' . $filename;

            Storage::disk('public')->put($path, $encoded);

            // 5. آپدیت دیتابیس
            DB::table('news')->where('id', $this->newsId)->update([
                'cover' => $path,
                'updated_at' => now()
            ]);

            Log::info("✅ [تصویر ذخیره شد]", ['path' => $path]);

        } catch (\Exception $e) {
            Log::error("❌ خطای پردازش تصویر", [
                'news_id' => $this->newsId,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    private function findImageUrl(): ?string
    {
        // الف) اولویت با JSON-LD (چون دقیق‌ترین است)
        $jsonImage = $this->config['news_selectors']['json_ld_image'] ?? null;
        if ($this->isValidUrl($jsonImage)) {
            return $this->normalizeUrl($jsonImage);
        }

        if (!$this->html) return null;
        $crawler = new Crawler($this->html);

        // ب) بررسی متاتگ تعریف شده در کانفیگ (cover_alt)
        // این بخش قبلاً فقط og:image را چک می‌کرد اما الان دینامیک است
        if (!empty($this->config['news_selectors']['cover_alt'])) {
            try {
                $selector = $this->config['news_selectors']['cover_alt'];
                $node = $crawler->filter($selector);
                if ($node->count() > 0) {
                    // برای متاتگ‌ها معمولا content است، برای بقیه src
                    $attr = str_contains($selector, 'meta') ? 'content' : 'src';
                    $val = $node->attr($attr);
                    if ($val) return $this->normalizeUrl($val);
                }
            } catch (\Exception $e) {}
        }

        // ج) فال‌بک به og:image استاندارد اگر در کانفیگ نبود یا پیدا نشد
        try {
            if ($crawler->filter('meta[property="og:image"]')->count() > 0) {
                $val = $crawler->filter('meta[property="og:image"]')->attr('content');
                if ($val) return $this->normalizeUrl($val);
            }
        } catch (\Exception $e) {}

        // د) بررسی سلکتورهای CSS (cover, cover_carousel)
        $cssSelectors = ['cover', 'cover_carousel'];
        foreach ($cssSelectors as $key) {
            if (!empty($this->config['news_selectors'][$key])) {
                try {
                    $selector = $this->config['news_selectors'][$key];
                    $node = $crawler->filter($selector);

                    if ($node->count() > 0) {
                        // تلاش برای پیدا کردن بهترین اتریبیوت عکس
                        $src = $node->attr('src')
                            ?? $node->attr('data-src')
                            ?? $node->attr('data-original')
                            ?? $node->attr('srcset'); // گاهی عکس‌ها در srcset هستند

                        if ($src) {
                            // اگر srcset بود، اولین url را بردار
                            if (str_contains($src, ',')) {
                                $src = explode(' ', trim(explode(',', $src)[0]))[0];
                            }
                            return $this->normalizeUrl($src);
                        }
                    }
                } catch (\Exception $e) {}
            }
        }

        return null;
    }

    /**
     * تبدیل لینک‌های نسبی به لینک‌های مطلق
     */
    private function normalizeUrl(?string $link): ?string
    {
        if (empty($link)) return null;

        $link = trim($link);

        // اگر خودش لینک کامل است
        if (str_starts_with($link, 'http')) return $link;

        // دریافت ریشه سایت از URL اصلی خبر
        $parsedUrl = parse_url($this->url);
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        // اگر با / شروع می‌شود (مثل /images/pic.jpg)
        if (str_starts_with($link, '/')) {
            return $baseUrl . $link;
        }

        // اگر فقط نام فایل است (مثل pic.jpg) - فرض بر این است که در مسیر جاری است
        // اما معمولا در سایت‌های خبری modern این حالت کمتر رخ می‌دهد، با این حال:
        return $baseUrl . '/' . $link;
    }

    /**
     * اعتبارسنجی اولیه (لینک نباید خالی باشد)
     * اعتبارسنجی دقیق‌تر بعد از نرمال‌سازی انجام می‌شود
     */
    private function isValidUrl($url): bool
    {
        return !empty($url) && is_string($url);
    }
}
