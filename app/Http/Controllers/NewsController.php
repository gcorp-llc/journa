<?php

namespace App\Http\Controllers;

use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = News::query()
                ->with(['newsSite', 'categories'])
                ->published()
                ->orderBy('published_at', 'desc');

            // فیلتر بر اساس دسته‌بندی
            if ($request->has('category')) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('slug', $request->category)
                      ->orWhere('categories.id', $request->category);
                });
            }

            // فیلتر بر اساس سایت خبری
            if ($request->has('site_id')) {
                $query->where('news_site_id', $request->site_id);
            }

            // جستجو
            if ($request->has('query')) {
                $searchTerm = $request->query('query');
                $locale = $request->query('locale', app()->getLocale());

                $query->where(function ($q) use ($searchTerm, $locale) {
                    $q->where("title->{$locale}", 'like', "%{$searchTerm}%")
                      ->orWhere("content->{$locale}", 'like', "%{$searchTerm}%");
                });
            }

            $perPage = $request->query('perPage', 15);
            $news = $query->paginate($perPage);

            return NewsResource::collection($news);
        } catch (\Exception $e) {
            Log::error('Failed to fetch news', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch news'], 500);
        }
    }

    public function all(Request $request)
    {
        // مشابه index اما احتمالا برای لیست‌های ساده‌تر یا بدون صفحه‌بندی (بسته به نیاز قبلی)
        return $this->index($request);
    }

    public function show($slug, Request $request)
    {
        try {
            $news = News::with(['newsSite', 'categories'])
                ->where('slug', $slug)
                ->published()
                ->firstOrFail();

            return new NewsResource($news);
        } catch (\Exception $e) {
            return response()->json(['error' => 'News not found'], 404);
        }
    }

    public function incrementViews($slug)
    {
        try {
            $news = News::where('slug', $slug)->firstOrFail();
            $news->increment('views');
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'News not found'], 404);
        }
    }
}
