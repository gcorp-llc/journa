<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('query');
        $locale = $request->query('locale', 'fa');
        $perPage = $request->query('perPage', 33);

        if (empty($query)) {
            return response()->json([
                'data' => [],
                'total' => 0,
                'message' => 'عبارت جستجو وارد نشده است.'
            ]);
        }

        $searchTerm = "%{$query}%";

        $news = DB::table('news')
            ->where('status', 'published')
            ->where(function($q) use ($searchTerm, $locale) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$locale}\"')) LIKE ?", [$searchTerm])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(content, '$.\"{$locale}\"')) LIKE ?", [$searchTerm]);
            })
            ->select(
                'id',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$locale}\"')) as title"),
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(content, '$.\"{$locale}\"')) as content"),
               'cover',
                'slug',
                'published_at'
            )
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $news->items(),
            'total' => $news->total(),
            'current_page' => $news->currentPage(),
            'last_page' => $news->lastPage(),
        ]);
    }
}
