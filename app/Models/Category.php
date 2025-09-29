<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations;
     /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['title','description' , 'slug', 'icon', 'parent_id','sort_order'];
    public $translatable = ['title','description'];
    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the news articles associated with this category.
     */
    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'category_news', 'category_id', 'news_id');
    }

    /**
     * Get the news site categories (if applicable).
     */
    public function newsSites(): HasMany
    {
        return $this->hasMany(NewsSiteCategory::class, 'category_id');
    }
}
