<?php

namespace App\Enums;

/**
 * Enum برای تعریف سایت‌های پشتیبانی شده برای خزش اطلاعات پزشکان.
 * این Enum به عنوان "منبع حقیقت" عمل کرده و از خطاهای رشته‌ای جلوگیری می‌کند.
 */
enum DoctorCrawlerSite: string
{
    case NOBAT = 'nobat';
    case DOCTORTO = 'doctorto';

    /**
     * بازگرداندن لیستی از تمام مقادیر سایت‌های پشتیبانی شده.
     * برای استفاده در اعتبارسنجی ورودی کامند.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
