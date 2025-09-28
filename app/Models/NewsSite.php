<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class NewsSite extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'description', 'logo_url', 'site_url'];

    public $translatable = ['name', 'description'];

    protected $casts = [
        'name' => 'array',
        'description' => 'array'
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(NewsSiteCategory::class, 'news_site_id');
    }
}
