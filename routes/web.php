<?php

use App\Http\Controllers\NewsWebController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;


Route::get('/redis-test', function () {
    $redis = Illuminate\Support\Facades\Redis::connection('default');
    $redis->set('laravel_test', 'ok');
    return $redis->get('laravel_test');
});




Route::group(['middleware' => 'firewall.all'], function () {

    Route::group(['middleware' => 'localisation'], function () {
        Volt::route('/', 'pages.home')->name('home');
        Volt::route('/search', 'pages.search')->name('search');
        Volt::route('/cat/{slug}', 'pages.category')->name('category');
        Volt::route('/cat/{category_slug}/{child_slug}', 'pages.child')->name('child');
        Volt::route('/news/{slug}', 'show.news')->name('news');
    });

    // مسیرهای زبان‌دار با prefix
    $locales = ['fa', 'en', 'ar'];
    foreach ($locales as $locale) {
        Route::group([
            'prefix' => $locale,
            'middleware' => "localisation:$locale"
        ], function () {
            Volt::route('/', 'pages.home')->name('home');
            Volt::route('/search', 'pages.search')->name('search');
            Volt::route('/cat/{slug}', 'pages.category')->name('category');
            Volt::route('/cat/{category_slug}/{child_slug}', 'pages.child')->name('child');
            Volt::route('/news/{slug}', 'show.news')->name('news');

        });
    }
});
