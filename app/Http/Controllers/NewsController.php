<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class NewsController extends Controller
{
    protected $perPage = 33;

    private function getLocale(Request $request)
    {
        $locale = $request->get('lang', $request->header('x-lang', 'fa'));
        return in_array($locale, ['fa', 'en', 'ar']) ? $locale : 'fa';
    }

    /**
     * لیست اخبار با پگینیت اختصاصی (فقط لینک صفحه بعد)
     */
    public function index(Request $request)
    {
        $locale = $this->getLocale($request);

        $news = DB::table('news')
            ->join('news_sites', 'news.news_site_id', '=', 'news_sites.id')
            ->where('news.status', 'published')
            ->select([
                'news.id',
                'news.slug',
                'news.cover',
                'news.views',
                'news.published_at',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(news.title, '$.$locale')) as title"),
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(news_sites.name, '$.$locale')) as news_site_name")
            ])
            ->orderBy('news.published_at', 'desc')
            ->simplePaginate($this->perPage);

        return response()->json([
            'data' => $news->items(),
            'next_page_url' => $news->nextPageUrl(),
        ]);
    }

    /**
     * نمایش کامل خبر - شامل تمام فیلدها
     */
    public function show(Request $request, $slug)
    {
        $locale = $this->getLocale($request);

        $news = DB::table('news')
            ->join('news_sites', 'news.news_site_id', '=', 'news_sites.id')
            ->where('news.slug', $slug)
            ->select([
                'news.id',
                'news.slug',
                'news.cover',
                'news.views',
                'news.published_at',
                'news.source_url',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(news.title, '$.$locale')) as title"),
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(news.content, '$.$locale')) as content"),
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(news_sites.name, '$.$locale')) as news_site_name")
            ])
            ->first();

        if (!$news) {
            return response()->json(['error' => 'خبر یافت نشد'], 404);
        }

        return response()->json(['data' => $news]);
    }

    public function incrementViews($slug)
    {
        DB::table('news')->where('slug', $slug)->increment('views');
        return response()->json(['success' => true]);
    }
}
