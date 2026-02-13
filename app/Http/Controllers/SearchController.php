<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $locale = $request->get('lang', $request->header('x-lang', 'fa'));
        $term = $request->query('q');

        if (!$term) return response()->json(['data' => [], 'next_page_url' => null]);

        $news = DB::table('news')
            ->join('news_sites', 'news.news_site_id', '=', 'news_sites.id')
            ->where('news.status', 'published')
            ->where(function ($query) use ($term, $locale) {
                // جستجوی مستقیم در فیلد JSON
                $query->where("news.title->{$locale}", 'like', "%{$term}%")
                    ->orWhere("news.content->{$locale}", 'like', "%{$term}%");
            })
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
            ->simplePaginate(33)
            ->appends($request->query());

        return response()->json([
            'data' => $news->items(),
            'next_page_url' => $news->nextPageUrl()
        ]);
    }
}
