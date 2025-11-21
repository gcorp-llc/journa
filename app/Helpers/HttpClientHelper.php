<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpClientHelper
{
    private const USER_AGENTS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:124.0) Gecko/20100101 Firefox/124.0',
    ];

    public static function fetchPage(string $url, array $options = []): array
    {
        $jobId = $options['job_id'] ?? uniqid('http_', true);
        $maxRetries = $options['max_retries'] ?? 5;
        $timeout = $options['timeout'] ?? 20;

        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                if ($attempt > 1) {
                    sleep(rand(3, 7));
                }

                $userAgent = self::USER_AGENTS[array_rand(self::USER_AGENTS)];
                $headers = self::generateRealisticHeaders($url, $userAgent);

                $response = Http::withHeaders($headers)
                    ->timeout($timeout)
                    ->withOptions([
                        'verify' => false,
                        'allow_redirects' => ['max' => 5, 'strict' => true, 'referer' => true],
                        'cookies' => true,
                        // اضافه کردن این خط برای حل مشکل دیکودینگ اگر Guzzle خودش مدیریت نکند
                        'decode_content' => true,
                    ])
                    ->get($url);

                if ($response->successful()) {
                    $body = $response->body();
                    if (self::isValidHtml($body)) {
                        return [
                            'success' => true,
                            'body' => $body,
                            'status' => $response->status(),
                            'attempts' => $attempt,
                        ];
                    }
                }

                if ($response->status() === 403 || $response->status() === 401) {
                    Log::warning("🚫 [دسترسی ممنوع 403]", ['url' => $url, 'attempt' => $attempt]);
                    if ($attempt < $maxRetries) continue;
                }

                if ($response->status() === 429) {
                    sleep(15);
                    continue;
                }

                throw new \Exception("HTTP Error: {$response->status()}");

            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("⚠️ [خطا در تلاش {$attempt}]", ['url' => $url, 'error' => $e->getMessage()]);
                if ($attempt < $maxRetries) continue;
            }
        }

        return [
            'success' => false,
            'error' => $lastException ? $lastException->getMessage() : 'Failed',
            'attempts' => $maxRetries,
        ];
    }

    private static function generateRealisticHeaders(string $url, string $userAgent): array
    {
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? '';

        return [
            'User-Agent' => $userAgent,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language' => 'en-US,en;q=0.9',

            // اصلاح مهم: حذف 'br' از لیست انکودینگ‌ها
            // فقط gzip و deflate را درخواست می‌کنیم که همه سرورها پشتیبانی می‌کنند
            'Accept-Encoding' => 'gzip, deflate',

            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Cache-Control' => 'max-age=0',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Host' => $host,
        ];
    }

    private static function isValidHtml(string $content): bool
    {
        if (empty($content)) return false;
        return preg_match('/<html|<body|<head|<!DOCTYPE/i', $content);
    }

    public static function downloadFile(string $url, array $options = []): ?string {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENTS[0],
                'Referer' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/',
            ])->withOptions(['verify'=>false])->get($url);
            return $response->successful() ? $response->body() : null;
        } catch (\Exception $e) { return null; }
    }
}
