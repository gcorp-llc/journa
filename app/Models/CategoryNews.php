<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryNews extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['news_id', 'category_id'];

      /**
     * Get the news that owns the category_news.
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class, 'news_id');
    }

    /**
     * Get the category that owns the category_news.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
     /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            // Ensure both news_id and category_id are set
            if (!$model->news_id || !$model->category_id) {
                throw new \Exception('Both news_id and category_id are required.');
            }
        });
    }

}
