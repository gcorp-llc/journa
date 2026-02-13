<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Cookie\CookieJar;
use Spatie\Browsershot\Browsershot;

trait InteractsWithHttp
{
    /**
     * لیست یوزر ایجنت‌های دقیقاً مشابه کروم واقعی (آپدیت شده نوامبر 2025)
     */
    private function getRandomUserAgent(): string
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
        ];
        return $agents[array_rand($agents)];
    }

    /**
     * دریافت محتوای صفحه با استفاده از مرورگر واقعی (Browsershot/Puppeteer)
     * برای عبور از سدهای ضد ربات
     */
    protected function getHtmlWithBrowsershot(string $url): string
    {
        return Browsershot::url($url)
            ->userAgent($this->getRandomUserAgent())
            ->timeout(60)
            ->waitUntilNetworkIdle()
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
            ->bodyHtml();
    }

    /**
     * ارسال درخواست HTTP با تنظیمات پیشرفته cURL برای رفع تایم‌اوت SSL
     */
    protected function sendRequest(string $url, string $method = 'get', array $options = [])
    {
        $maxRetries = $options['retries'] ?? 4;
        $timeout = $options['timeout'] ?? 40; // افزایش تایم‌اوت کلی
        $jobId = $options['job_id'] ?? uniqid('req_', true);

        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // تاخیر بین تلاش‌ها
                if ($attempt > 1) {
                    $delay = rand(5, 10);
                    // اگر خطای 429 بود، صبر بیشتر
                    if (isset($lastException) && str_contains($lastException->getMessage(), '429')) {
                        $delay = rand(30, 60);
                    }
                    sleep($delay);
                }

                $cookieJar = new CookieJar();

                $parsedUrl = parse_url($url);
                $host = $parsedUrl['host'] ?? '';
                $scheme = $parsedUrl['scheme'] ?? 'https';

                // رفرر هوشمند: اگر تلاش اول است گوگل، در غیر این صورت صفحه اصلی سایت
                $referer = ($attempt === 1)
                    ? 'https://www.google.com/'
                    : "$scheme://$host/";

                $headers = [
                    'User-Agent' => $this->getRandomUserAgent(),
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                    'Cache-Control' => 'max-age=0',
                    'Sec-Fetch-Dest' => 'document',
                    'Sec-Fetch-Mode' => 'navigate',
                    'Sec-Fetch-Site' => 'cross-site', // تغییر به cross-site چون رفرر گوگل است
                    'Sec-Fetch-User' => '?1',
                    'Host' => $host,
                    'Referer' => $referer
                ];

                $client = Http::withHeaders($headers)
                    ->timeout($timeout) // تایم‌اوت کلی درخواست
                    ->connectTimeout(15) // تایم‌اوت اتصال اولیه (هندشیک)
                    ->withOptions([
                        'verify' => false,
                        'http_errors' => false,
                        'allow_redirects' => [
                            'max' => 5,
                            'strict' => true,
                            'referer' => true,
                            'track_redirects' => true
                        ],
                        'cookies' => $cookieJar,
                        'decode_content' => true,

                        // 🔥 تنظیمات حیاتی cURL برای رفع Error 28 و کندی ویندوز 🔥
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // اجبار به استفاده از IPv4
                            CURLOPT_TCP_KEEPALIVE => 1,           // زنده نگه داشتن اتصال TCP
                            CURLOPT_TCP_KEEPIDLE => 10,
                            CURLOPT_TCP_KEEPINTVL => 10,
                            CURLOPT_DNS_CACHE_TIMEOUT => 120,     // کش کردن DNS
                        ]
                    ]);

                $response = match (strtolower($method)) {
                    'get' => $client->get($url),
                    'head' => $client->head($url),
                    default => $client->get($url),
                };

                // موفقیت
                if ($response->status() === 200) {
                    return $response;
                }

                // مدیریت 404
                if ($response->status() === 404) {
                    throw new \Exception("صفحه یافت نشد (404)");
                }

                // مدیریت بلاک شدن (403/401)
                if (in_array($response->status(), [403, 401])) {
                    Log::warning("🚫 [دسترسی ممنوع {$response->status()} - تلاش {$attempt}]", ['job_id' => $jobId, 'url' => $url]);
                    continue; // تلاش مجدد
                }

                // مدیریت Rate Limit
                if ($response->status() === 429) {
                    Log::warning("⏳ [درخواست بیش از حد 429]", ['job_id' => $jobId]);
                    throw new \Exception("Rate Limit 429");
                }

                throw new \Exception("خطای HTTP: " . $response->status());

            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("⚠️ [خطای اتصال]", [
                    'job_id' => $jobId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);
            }
        }

        throw $lastException ?? new \Exception("خطای ناشناخته پس از {$maxRetries} تلاش");
    }
}
