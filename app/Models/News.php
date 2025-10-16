<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class News extends Model
{
    // استفاده از پکیج برای مدیریت فیلدهای چندزبانه
    use HasTranslations;

    protected $fillable = [
        'title',
        'content',
        'cover',
        'slug',
        'status',
        'views',
        'published_at',
        'source_url',
        'news_site_id'
    ];

    /**
     * فیلدهایی که باید چندزبانه باشند.
     * @var array
     */
    public $translatable = ['title', 'content'];

    /**
     * کست کردن اتریبیوت‌ها به نوع‌های مشخص.
     * @var array
     */
    protected $casts = [
       
        'published_at' => 'datetime',
        'title'=>'json:unicode',
        'content'=>'json:unicode'
    ];

    /**
     * رابطه با مدل سایت خبری.
     */
    public function newsSite()
    {
        return $this->belongsTo(NewsSite::class);
    }

    /**
     * رابطه چند به چند با دسته‌بندی‌ها.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_news', 'news_id', 'category_id');
    }

    /**
     * <<<< بهبود: تعریف کلید مورد استفاده برای Route Model Binding.
     * با این متد، لاراول به جای id از slug برای پیدا کردن خبر در URL استفاده می‌کند.
     * مثلاً در Route::get('/news/{news}', ...)، لاراول به دنبال slug می‌گردد.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * <<<< بهبود: یک Query Scope برای فیلتر کردن اخبار منتشر شده.
     * می‌توانید به این صورت از آن استفاده کنید: News::published()->get()
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * <<<< بهبود: یک Accessor برای ایجاد خلاصه‌ای از محتوا.
     * در فایل Blade می‌توانید به سادگی از $news->excerpt استفاده کنید.
     *
     * @return string
     */
    public function getExcerptAttribute(): string
    {
        // محتوای ترجمه شده را بر اساس زبان فعلی برنامه دریافت می‌کند
        $content = $this->getTranslation('content', app()->getLocale());

        // تگ‌های HTML را حذف کرده و به 150 کاراکتر محدود می‌کند.
        return Str::limit(strip_tags($content), 330);
    }


}
