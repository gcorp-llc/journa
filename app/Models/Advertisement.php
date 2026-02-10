<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;

class Advertisement extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'subject',
        'cover',
        'content',
        'destination_url',
        'start_date',
        'end_date',
        'max_clicks',
        'max_impressions',
        'current_impressions',
        'current_clicks',
        'is_active'
    ];

    // فیلدهای قابل ترجمه بر اساس پکیج Spatie
    public $translatable = ['title', 'subject', 'content'];

    protected $casts = [
        'title' => 'array',
        'subject' => 'array',
        'content' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Scope برای فیلتر تبلیغات فعال و معتبر (Query Building)
     * این متد سرعت کوئری‌ها را در کنترلر بالا می‌برد
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_impressions')
                    ->orWhereColumn('current_impressions', '<', 'max_impressions');
            })
            ->where(function ($q) {
                $q->whereNull('max_clicks')
                    ->orWhereColumn('current_clicks', '<', 'max_clicks');
            });
    }
}
