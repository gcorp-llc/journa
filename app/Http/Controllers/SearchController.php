<?php

namespace App\Http\Controllers;

use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $queryText = $request->query('query');
        if (empty($queryText)) {
            return response()->json([
                'data' => [],
                'message' => 'عبارت جستجو وارد نشده است.'
            ]);
        }

        $locale = $request->query('locale', app()->getLocale());
        $perPage = $request->query('perPage', 15);

        $news = News::query()
            ->with(['newsSite', 'categories'])
            ->published()
            ->where(function($q) use ($queryText, $locale) {
                $q->where("title->{$locale}", 'like', "%{$queryText}%")
                  ->orWhere("content->{$locale}", 'like', "%{$queryText}%");
            })
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);

        return NewsResource::collection($news);
    }
}
