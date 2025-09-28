<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        try {
            // استخراج پارامترها
            $parent = $request->query('parent');
            $child = $request->query('child');
            $query = $request->query('query');
            $page = max(1, (int) $request->query('page', 1));
            $perPage = max(1, (int) $request->query('perPage', 33));
            $locale = $request->query('locale', 'fa');
            $offset = ($page - 1) * $perPage;

            // اعتبارسنجی locale
            $allowedLocales = ['en', 'fa', 'ar'];
            if (!in_array($locale, $allowedLocales)) {
                return response()->json(['error' => 'Invalid locale'], 400);
            }

            // اعتبارسنجی دسته‌بندی
            if ($parent || $child) {
                $categorySlug = $child ?: $parent;
                $categoryExists = DB::table('categories')->where('slug', $categorySlug)->exists();
                if (!$categoryExists) {
                    return response()->json(['error' => 'Category not found'], 404);
                }
            }

            // ساخت کوئری پایه با DISTINCT برای جلوگیری از تکرار
            $baseQuery = DB::table('news')
                ->select(
                    DB::raw('DISTINCT news.id'),
                    DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(news.title, '$.\"{$locale}\"')), 'No title') as title"),
                    DB::raw("LEFT(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(news.content, '$.\"{$locale}\"')), 'No content'), 100) as content"),
                    'news.slug',
                    'news.published_at',
                    'news.cover'
                )
                ->where('status', 'published');

            // اضافه کردن جستجو
            if ($query) {
                $searchTerm = "%{$query}%";
                $baseQuery->where(function ($q) use ($searchTerm, $locale) {
                    $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(news.title, '$.\"{$locale}\"')) LIKE ?", [$searchTerm])
                      ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(news.content, '$.\"{$locale}\"')) LIKE ?", [$searchTerm]);
                });
            }

            // فیلتر دسته‌بندی
            if ($parent || $child) {
                $baseQuery->join('category_news', 'news.id', '=', 'category_news.news_id')
                    ->join('categories', 'category_news.category_id', '=', 'categories.id')
                    ->where('categories.slug', $child ?: $parent);
            }

            // دریافت داده‌های صفحه‌بندی‌شده
            $data = $baseQuery->orderBy('news.published_at', 'desc')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            // محاسبه تعداد کل
            $totalQuery = DB::table('news')->where('status', 'published');
            if ($query) {
                $totalQuery->where(function ($q) use ($searchTerm, $locale) {
                    $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(news.title, '$.\"{$locale}\"')) LIKE ?", [$searchTerm])
                      ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(news.content, '$.\"{$locale}\"')) LIKE ?", [$searchTerm]);
                });
            }
            if ($parent || $child) {
                $totalQuery->join('category_news', 'news.id', '=', 'category_news.news_id')
                    ->join('categories', 'category_news.category_id', '=', 'categories.id')
                    ->where('categories.slug', $child ?: $parent);
            }
            $total = $totalQuery->count();

            return response()->json([
                'data' => $data,
                'next_page_url' => $data->count() === $perPage ? $request->fullUrlWithQuery(['page' => $page + 1]) : null,
                'total' => $total,
            ], 200);
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                Log::error('Failed to fetch news', ['message' => $e->getMessage()]);
            }
            return response()->json(['error' => 'Failed to fetch news'], 500);
        }
    }

    public function show($slug, Request $request)
    {
        try {
            $locale = $request->query('locale', 'fa');
            $allowedLocales = ['en', 'fa', 'ar'];
            if (!in_array($locale, $allowedLocales)) {
                return response()->json(['error' => 'Invalid locale'], 400);
            }

            $news = DB::table('news')
                ->leftJoin('news_sites', 'news.news_site_id', '=', 'news_sites.id')
                ->where('news.slug', $slug)
                ->where('news.status', 'published')
                ->select(
                    'news.id',
                    DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(news.title, '$.\"{$locale}\"')), 'No title') as title"),
                    DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(news.content, '$.\"{$locale}\"')), 'No content') as content"),
                    'news.slug',
                    'news.published_at',
                    'news.cover',
                    'news.views',
                    'news.source_url',
                    DB::raw("CASE WHEN news_sites.logo_url IS NOT NULL AND news_sites.logo_url != '' THEN CONCAT('" . asset('storage') . "/', news_sites.logo_url) ELSE NULL END as logo_url"),
                    DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(news_sites.name, '$.\"{$locale}\"')), 'Unknown') as news_site_name")
                )
                ->first();

            if (!$news) {
                return response()->json(['error' => 'News not found'], 404);
            }

            return response()->json(['data' => $news], 200);
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                Log::error('Failed to fetch news', ['message' => $e->getMessage()]);
            }
            return response()->json(['error' => 'Failed to fetch news'], 500);
        }
    }

    public function incrementViews($slug)
    {
        try {
            $news = DB::table('news')->where('slug', $slug)->first();
            if (!$news) {
                return response()->json(['error' => 'News not found'], 404);
            }
            DB::table('news')->where('slug', $slug)->increment('views');
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                Log::error('Failed to increment views', ['message' => $e->getMessage(), 'slug' => $slug]);
            }
            return response()->json(['error' => 'Failed to increment views'], 500);
        }
    }
}
