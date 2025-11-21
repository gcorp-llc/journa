<?php

use function Livewire\Volt\{layout, state, mount};
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Artesaos\SEOTools\Facades\{SEOMeta, OpenGraph};
use Morilog\Jalali\Jalalian;
// خط زیر حذف شد: use DateTime;

layout('components.layouts.app');
state(['news' => null, 'isLoading' => true,'lastnews'=>
    fn () => \App\Models\News::latest()
        ->take(15)
        ->get()  ]);

// Helper function for locale-specific date formatting
function formatDateByLocale($date, $locale)
{
    return match ($locale) {
        'fa' => Jalalian::fromDateTime($date)->format('Y/m/d'),
        // کلاس DateTime همچنان در اینجا قابل استفاده است
        'ar' => (new DateTime($date))->format('Y/m/d'),
        default => $date->format('Y/m/d'),
    };
}

mount(function ($slug) {
    // بقیه کد بدون تغییر
    $locale = request()->query('locale', 'fa');
    $allowedLocales = ['en', 'fa', 'ar'];

    if (!in_array($locale, $allowedLocales)) {
        abort(400, 'Invalid locale');
    }

    // Fetch news first
    $news = \App\Models\News::firstWhere('slug', $slug);
    // ✅ Fetch 10 random latest news, excluding current one

    if (!$news) {
        abort(404, 'News not found');
    }

    // Increment views after confirming news exists
    DB::table('news')->where('slug', $slug)->increment('views');

    $this->news = $news;
    $this->isLoading = false;

    // Extract description once to avoid repetition
    $description = Str::limit(strip_tags($news->content), 150, '...');
    $title=Str::limit(strip_tags($news->title), 50, '...');
    // Set SEO metadata
    SEOMeta::setTitle($title);
    SEOMeta::setDescription($description);
    SEOMeta::setCanonical(request()->url());
    SEOMeta::addMeta('twitter:card', 'summary_large_image');
    SEOMeta::addMeta('twitter:title', $title);
    SEOMeta::addMeta('twitter:description', $description);

    // Set Open Graph metadata
    OpenGraph::setTitle($title);
    OpenGraph::setDescription($description);
    OpenGraph::setUrl(request()->url());
    OpenGraph::addProperty('type', 'webpage');

    if ($news->cover) {
        OpenGraph::addImage(Storage::url($news->cover));
    }
});
?>

<div class="relative min-h-screen rounded-2xl bg-white/50 backdrop-blur-md my-4 sm:my-8">
    <!-- Decorative Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-0 w-96 h-96 bg-amber-500 rounded-full opacity-10 blur-3xl animate-pulse"></div>
        <div class="absolute top-1/3 right-0 w-96 h-96 bg-cyan-500 rounded-full opacity-5 blur-3xl"></div>
    </div>

    <!-- Main Content -->
    <article class="relative z-10 container mx-auto px-4 sm:px-2 py-10 md:py-20">
        <div class="max-w-4xl mx-auto">
            <!-- Featured Image -->
            <figure class="mb-10 rounded-2xl overflow-hidden shadow-2xl">
                <img
                    src="{{ $news->cover ? Storage::url($news->cover) : asset('placeholder.png') }}"
                    alt="{{ $news->title }}"
                    class="w-full h-80 md:h-[500px] object-cover transition-transform duration-500 hover:scale-105"
                    loading="lazy"
                />
            </figure>
            <!-- Header Section -->
            <header class="mb-8 md:mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-6 leading-tight drop-shadow-lg">
                    {{ $news->title }}
                </h1>

                <!-- Meta Information -->
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    @if($news->newsSite)
                        <div class="badge badge-soft badge-primary text-xs font-medium px-3 py-2">
                            {{ $news->newsSite->name }}
                        </div>
                    @endif

                    @if($news->published_at)
                        <div class="badge badge-soft badge-secondary text-xs font-medium px-3 py-2">
                             {{ formatDateByLocale($news->published_at, app()->getLocale()) }}
                        </div>
                    @endif

                    @if($news->views !== null)
                        <div class="badge badge-soft badge-accent text-xs font-medium px-3 py-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                            </svg>
                            {{ trans_choice('news.views', $news->views, ['count' => number_format($news->views)], app()->getLocale()) }}
                        </div>
                    @endif
                </div>
            </header>



            <!-- Advertisement Slider -->
            <div class="my-10 rounded-xl overflow-hidden">
                <livewire:components.advertismentslider />
            </div>

            <!-- Article Content -->
            <div class="prose  prose-invert prose-lg max-w-none text-justify text-lg text-slate-800

                  mb-10">
                {!! $news->content !!}
            </div>

            <!-- Source Link -->
            @if($news->source_url)
                <div class="pt-10 border-t border-gray-700">
                    <a
                        href="{{ $news->source_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-accent btn-lg gap-2 hover:shadow-xl transition-all duration-300"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        {{ __('news.source', [], app()->getLocale()) }}
                    </a>
                </div>
            @endif
        </div>
    </article>

    @if($lastnews && $lastnews->count())
        <section class="container mx-auto px-4 py-12">
            <h2 class="text-3xl font-bold">{{ __('menu.home.title') }}</h2>
            <p>{{ __('menu.home.description') }}</p>
            <div class="w-full">
                <div class="carousel carousel-center rounded-2xl w-full my-3 space-x-4 p-4 bg-gradient-to-r from-slate-900 to-sky-950">
                    @forelse($lastnews as $item)
                        <div class="carousel-item snap-center w-80 flex-shrink-0">
                                <livewire:components.news-card :news="$item" />
                        </div>
                    @empty
                        <div class="w-full flex items-center justify-center h-40 text-gray-500">
                            <span>{{ __('messages.no_ads') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>



        </section>
    @endif

</div>
