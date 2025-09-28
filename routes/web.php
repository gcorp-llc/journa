<?php

use App\Http\Controllers\NewsWebController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::group(['middleware' => 'firewall.all'], function () {

    // تعریف یکبار برای همه زبان‌ها
    $routes = function () {

        // Volt (News Detail)
        Volt::route('/', 'pages.home')->name('home');
        Volt::route('/search', 'pages.search')->name('search');
        Volt::route('/cat/{slug}', 'pages.category')->name('category');
        Volt::route('/cat/{category_slug}/{child_slug}', 'pages.child')->name('child');
        Volt::route('/news/{slug}', 'show.news')->name('news');
    };

    // مسیر اصلی بدون prefix
    $routes();

    // مسیرهای زبان‌دار (fa, en, ar)
    Route::prefix('{lang}')
        ->where(['lang' => 'fa|en|ar'])
        ->middleware('localisation')
        ->group($routes);
});

Route::get('/translate-category-titles', function () {
    set_time_limit(900000);
    try {
        $translationService = app(\App\Services\TranslationService::class);
        $categories = \App\Models\Category::all();

        $total = $categories->count();

        $processed = 0;

        foreach ($categories as $category) {
            try {

                // Skip if title is empty
                if (empty(trim($category->title))) {
                    Log::warning("Skipping category ID {$category->id}: Empty title");
                    continue;
                }

                // Translate title to all supported languages
                $translations = $translationService->translateToAll($category->title);

                // Store translations in the database
                $category->update([
                    'title'=>$translations
                ]);

                $processed++;
            } catch (\Exception $e) {
                Log::error("Failed to translate category ID {$category->id}", ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Translated and stored titles for {$processed} of {$total} categories.",
        ], 200);
    } catch (\Exception $e) {
        Log::error("Category title translation failed", ['error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to translate category titles.',
            'error' => $e->getMessage(),
        ], 500);
    }
})->name('categories.translate.titles');
