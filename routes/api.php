<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('all', [NewsController::class, 'all'])->name('news.all');
    Route::get('news', [NewsController::class, 'index'])->name('news.index');
    Route::get('news/{slug}', [NewsController::class, 'show'])->name('news.show');

    Route::get('ads', [AdController::class, 'index'])->name('ads.index');
    Route::post('/ads/{id}/click', [AdController::class, 'click'])->name('ads.click');
    
    Route::get('search', [SearchController::class, 'search'])->name('search.index');

    Route::post('news/{slug}/increment-views', [NewsController::class, 'incrementViews'])->name('news.incrementViews');

});
