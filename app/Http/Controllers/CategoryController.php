<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    private function getLocale(Request $request)
    {
        $locale = $request->get('lang', $request->header('x-lang', 'fa'));
        return in_array($locale, ['fa', 'en', 'ar']) ? $locale : 'fa';
    }

    public function menu(Request $request)
    {
        $locale = $this->getLocale($request);

        $categories = DB::table('categories')
            ->whereNull('parent_id')
            ->select([
                'id',
                'slug',
                'icon',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.$locale')) as title")
            ])
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json(['data' => $categories]);
    }

    /**
     * اخبار دسته بندی با پگینیت محدود شده (فقط لینک صفحه بعد)
     */
    public function show(Request $request, $slug)
    {
        $locale = $this->getLocale($request);

        $category = DB::table('categories')->where('slug', $slug)->first();
        if (!$category) return response()->json(['error' => 'دسته بندی یافت نشد'], 404);

        $news = DB::table('news')
            ->join('category_news', 'news.id', '=', 'category_news.news_id')
            ->join('news_sites', 'news.news_site_id', '=', 'news_sites.id')
            ->where('category_news.category_id', $category->id)
            ->where('news.status', 'published')
            ->select([
                'news.id',
                'news.slug',
                'news.cover',
                'news.published_at',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(news.title, '$.$locale')) as title"),
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(news_sites.name, '$.$locale')) as news_site_name")
            ])
            ->orderBy('news.published_at', 'desc')
            ->simplePaginate(33);

        return response()->json([
            'data' => $news->items(),
            'next_page_url' => $news->nextPageUrl(),
            'category_title' => json_decode($category->title)->$locale ?? $category->slug
        ]);
    }
}
