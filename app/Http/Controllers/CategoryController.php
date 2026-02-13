<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
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

        $category = Category::where('slug', $slug)->first();
        if (!$category) return response()->json(['error' => 'دسته بندی یافت نشد'], 404);

        // دریافت شناسه تمام دسته‌بندی‌های فرزند به صورت بازگشتی
        $categoryIds = $this->getAllCategoryIds($category);

        $news = News::query()
            ->join('category_news', 'news.id', '=', 'category_news.news_id')
            ->join('news_sites', 'news.news_site_id', '=', 'news_sites.id')
            ->whereIn('category_news.category_id', $categoryIds)
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
            ->groupBy('news.id') // جلوگیری از تکرار خبرهایی که در چند زیردسته هستند
            ->simplePaginate(33);

        return response()->json([
            'data' => $news->items(),
            'next_page_url' => $news->nextPageUrl(),
            'category_title' => $category->getTranslation('title', $locale) ?? $category->slug
        ]);
    }

    /**
     * متد کمکی برای دریافت تمام شناسه‌های دسته‌بندی (خودش و فرزندانش در تمام سطوح)
     */
    private function getAllCategoryIds($category)
    {
        $ids = [$category->id];

        // لود کردن فرزندان اگر قبلاً لود نشده‌اند (به صورت بازگشتی)
        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->getAllCategoryIds($child));
        }

        return array_unique($ids);
    }
}
