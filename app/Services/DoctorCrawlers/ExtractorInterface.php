<?php

namespace App\Services\DoctorCrawlers;

use Symfony\Component\DomCrawler\Crawler;

/**
 * اینترفیسی برای تمام کلاس‌های استخراج کننده اطلاعات پزشک.
 * هر کلاس باید این متد را پیاده‌سازی کند تا داده‌های ساختاریافته را برگرداند.
 */
interface ExtractorInterface
{
    /**
     * استخراج اطلاعات پزشک از محتوای HTML.
     *
     * @param Crawler $crawler آبجکت DomCrawler برای پیمایش HTML
     * @param string $url آدرس صفحه‌ای که در حال پردازش است
     * @return array|null آرایه‌ای از داده‌های پزشک یا null در صورت شکست
     */
    public function extract(Crawler $crawler, string $url): ?array;
}
