<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdvertisementResource;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdController extends Controller
{
    public function index(Request $request)
    {
        try {
            $ads = Advertisement::query()
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
                ->latest()
                ->get();

            // ثبت Impression
            Advertisement::whereIn('id', $ads->pluck('id'))->increment('current_impressions');

            return AdvertisementResource::collection($ads);
        } catch (\Exception $e) {
            Log::error('Failed to fetch ads', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch advertisements'], 500);
        }
    }

    public function click(Request $request, $id)
    {
        try {
            $ad = Advertisement::where('id', $id)->where('is_active', true)->firstOrFail();
            $ad->increment('current_clicks');
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Advertisement not found'], 404);
        }
    }
}
