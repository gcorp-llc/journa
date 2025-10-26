<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class NewsSiteCategory extends Model
{

    protected $fillable = ['news_site_id', 'category_id', 'category_url'];

    public function news(): BelongsTo
    {
        return $this->belongsTo(NewsSite::class, 'news_site_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
