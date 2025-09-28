<?php

namespace App\Enums;

enum Locale: string
{
    case FA = 'fa';
    case EN = 'en';
    case AR = 'ar';

    /**
     * یک پیشوند برای URL بر اساس زبان برمی‌گرداند.
     * برای زبان فارسی (پیش‌فرض) پیشوندی وجود ندارد.
     */
    public function urlPrefix(): string
    {
        return match ($this) {
            self::FA => '', // پیش‌فرض
            default => $this->value,
        };
    }
}
