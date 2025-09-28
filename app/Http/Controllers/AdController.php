<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdController extends Controller
{
    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $locale = $request->query('locale', 'fa');
            $offset = ($page - 1) * $limit;

            $cacheKey = "ads_{$locale}_{$page}_{$limit}";

            $ads = DB::table('advertisements')
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('max_impressions')
                        ->orWhereColumn('current_impressions', '<', 'max_impressions');
                })
                ->where(function ($query) {
                    $query->whereNull('max_clicks')
                        ->orWhereColumn('current_clicks', '<', 'max_clicks');
                })
                ->select(
                    'id',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$locale}\"')) as title"),
                    DB::raw("CONCAT('" . asset('storage') . "/', cover) as cover"),
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(subject, '$.\"{$locale}\"')) as subject"),
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(content, '$.\"{$locale}\"')) as content"),
                    'destination_url',
                )
                ->offset($offset)
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => $ads,
                'success' => true,
            ], 200, [
                'Cache-Control' => 'public, max-age=300',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch advertisements',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function click(Request $request, $id)
    {
        try {
            $ad = DB::table('advertisements')
                ->where('id', $id)
                ->where('is_active', true)
                ->first();

            if (!$ad) {
                return response()->json(
                    ['error' => 'Advertisement not found', 'success' => false],
                    404
                );
            }

            DB::table('advertisements')
                ->where('id', $id)
                ->increment('current_clicks');

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['error' => 'Failed to track click', 'message' => $e->getMessage()],
                500
            );
        }
    }
}
