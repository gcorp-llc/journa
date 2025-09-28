<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Advertisement extends Model
{
    use HasTranslations;
    protected $fillable = ['title','subject', 'cover','content','destination_url','start_date',
    'end_date','max_clicks','max_impressions','current_impressions','current_clicks','is_active'];

    public $translatable = ['title', 'subject','content'];

    protected $casts = [
        'title' => 'array',
        'subject' => 'array',
        'published_at' => 'datetime',
    ];
}
