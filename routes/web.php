<?php

use App\Http\Controllers\NewsWebController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'firewall.all'], function () {

    Route::group(['middleware' => 'localisation'], function () {
        Route::livewire('/', 'pages.home')->name('home');
        Route::livewire('/search', 'pages.search')->name('search');
        Route::livewire('/cat/{slug}', 'pages.category')->name('category');
        Route::livewire('/cat/{category_slug}/{child_slug}', 'pages.child')->name('child');
        Route::livewire('/news/{slug}', 'show.news')->name('news');
    });

    // مسیرهای زبان‌دار با prefix
    $locales = ['fa', 'en', 'ar'];
    foreach ($locales as $locale) {
        Route::group([
            'prefix' => $locale,
            'middleware' => "localisation:$locale"
        ], function () {
            Route::livewire('/', 'pages.home')->name('home');
            Route::livewire('/search', 'pages.search')->name('search');
            Route::livewire('/cat/{slug}', 'pages.category')->name('category');
            Route::livewire('/cat/{category_slug}/{child_slug}', 'pages.child')->name('child');
            Route::livewire('/news/{slug}', 'show.news')->name('news');

        });
    }
});
