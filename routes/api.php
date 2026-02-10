<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // News
    Route::get('news', [NewsController::class, 'index']);
    Route::get('news/{slug}', [NewsController::class, 'show']);
    Route::post('news/{slug}/increment-views', [NewsController::class, 'incrementViews']);

    // Menu & Category
    Route::get('menu', [CategoryController::class, 'menu']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);

    // Search
    Route::get('search', [SearchController::class, 'search']);

    // Ads
    Route::get('ads', [AdController::class, 'index']);
    Route::post('ads/{id}/click', [AdController::class, 'click']);
});
