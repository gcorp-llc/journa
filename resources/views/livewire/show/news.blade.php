<?php

use function Livewire\Volt\{layout,state, mount};
use Illuminate\Support\Facades\DB;
layout('components.layouts.app');
state(['news' => null]);

mount(function ($slug) {
    $locale = request()->query('locale', 'fa');
    $allowedLocales = ['en', 'fa', 'ar'];

    if (!in_array($locale, $allowedLocales)) {
        abort(400, 'Invalid locale');
    }

    // افزایش ویو
    DB::table('news')
        ->where('slug', $slug)
        ->increment('views');

    // واکشی خبر
    $this->news =\App\Models\News::firstWhere('slug',$slug);

    if (!$this->news) {
        abort(404, 'News not found');
    }
});
?>

<div class="relative container mx-auto p-4 sm:p-6 mt-12">
        <div class="absolute rounded-3xl glass top-0 left-0 w-full h-[70vh] sm:h-[60vh] bg-gradient-to-b from-amber-600 to-transparent z-10 pointer-events-none shadow-none"></div>

        <article class="prose prose-lg max-w-3xl mx-auto relative z-20 {{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'rtl' : 'ltr' }}">
            <h1 class="text-3xl font-bold my-4 leading-tight text-white drop-shadow-md">
                {{ $news->title }}
            </h1>

            <div class="flex items-center justify-between text-sm text-white mb-6 drop-shadow-sm">
                <div class="flex items-center gap-2">
                    @if($news->news_site_name)
                        <span class="font-medium">{{ $news->news_site_name }} |</span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @if($news->views !== null)
                        <span>{{ trans_choice('news.views', $news->views, ['count' => $news->views], app()->getLocale()) }}</span>
                    @endif

                    @if($news->source_url)
                        <a href="{{ $news->source_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:underline">
                            {{ __('news.source', [], app()->getLocale()) }}
                        </a>
                    @endif
                </div>
            </div>

            @if ($news->cover)
                <img src="{{ Storage::url($news->cover) }}"
                     alt="{{ $news->title }}"
                     class="rounded-lg shadow-sm mb-6 w-full h-[300] md:h[500]"
                     loading="lazy">
            @else
                <img src="{{ asset('/placeholder.png') }}"
                     alt="{{ $news->title }}"
                     class="rounded-lg shadow-sm mb-6 w-full h-[300] md:h[500]"
                     loading="lazy">
            @endif

            <div class="content text-justify leading-relaxed text-gray-700 text-lg">
                {!! $news->content !!}
            </div>
        </article>
    </div>

