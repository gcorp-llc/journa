<?php

namespace App\Jobs;

use App\Traits\InteractsWithHttp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessNewsImageJob implements ShouldQueue
{
    use Queueable, InteractsWithHttp;

    private const STORAGE_PATH = 'content_images';

    public function __construct(
        private readonly int $newsId,
        private readonly string $siteName,
        private readonly string $imageUrl,
        private readonly string $slug,
    ) {
        $this->onQueue('images');
    }

    public function handle(): void
    {
        try {
            // چک کردن اینکه آیا تصویر قبلاً پردازش شده؟ (برای جلوگیری از تکرار)
            $existing = DB::table('news')->where('id', $this->newsId)->value('cover');
            if ($existing && str_contains($existing, self::STORAGE_PATH)) {
                return;
            }

            Log::info('🖼️ شروع دانلود تصویر خبر', [
                'news_id' => $this->newsId,
                'url' => $this->imageUrl,
            ]);

            // اضافه کردن User-Agent برای جلوگیری از مسدود شدن توسط CDNها
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Referer' => parse_url($this->imageUrl, PHP_URL_SCHEME) . '://' . parse_url($this->imageUrl, PHP_URL_HOST)
            ])->timeout(15)->get($this->imageUrl);

            if ($response->failed()) {
                throw new \Exception("HTTP Error: " . $response->status());
            }

            $imageContent = $response->body();

            // اعتبارسنجی ساده محتوا
            if (strlen($imageContent) < 1000) {
                throw new \Exception("فایل دانلود شده بسیار کوچک است و احتمالاً تصویر نیست.");
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageContent);

            // ریسایز هوشمند
            if ($image->width() > 1200) {
                $image->scaleDown(width: 1200);
            }

            // تبدیل به WebP
            $encoded = $image->toWebp(quality: 80);

            $dateFolder = now()->format('Y-m-d');
            // تمیز کردن نام فایل از کاراکترهای غیرمجاز
            $safeSlug = preg_replace('/[^a-z0-9\-]+/', '-', strtolower($this->slug));
            $filename = trim($safeSlug, '-') . '-' . uniqid() . '.webp';
            $path = self::STORAGE_PATH . '/' . $dateFolder . '/' . $filename;

            Storage::disk('public')->put($path, $encoded);

            DB::table('news')
                ->where('id', $this->newsId)
                ->update([
                    'cover' => $path,
                    'updated_at' => now(),
                ]);

            Log::info('✅ تصویر ذخیره شد', ['path' => $path]);

        } catch (\Exception $e) {
            Log::error('❌ شکست در پردازش تصویر', [
                'news_id' => $this->newsId,
                'url' => $this->imageUrl,
                'msg' => $e->getMessage(),
            ]);
        }
    }
}
