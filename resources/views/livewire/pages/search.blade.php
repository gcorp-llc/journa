<?php

use Livewire\Volt\Component;
use App\Models\News;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;

new class extends Component {
    #[Url]
    public $search = '';
    public $searchInput = '';
    public $errorMessage = '';
    public $locale;
    public $news;
    public $totalResults = 0;
    public $hasSearched = false;

    public function mount()
    {
        $this->locale = app()->getLocale();
        $this->searchInput = $this->search;

        if (!empty($this->search)) {
            $this->performSearch();
        }

        $title = __('search.title');
        $description = __('search.resultsTitle', ['query' => $this->search ?: __('search.placeholder')]);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(request()->url());
        SEOMeta::addMeta('robots', 'index, follow');
        SEOMeta::addMeta('viewport', 'width=device-width, initial-scale=1');

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('type', 'webpage');
        OpenGraph::addProperty('locale', $this->locale);

        SEOMeta::addMeta('twitter:card', 'summary_large_image');
        SEOMeta::addMeta('twitter:title', $title);
        SEOMeta::addMeta('twitter:description', $description);
    }

    public function updateSEO(string $query): void
    {
        $title = $query ? __('search.resultsTitle', ['query' => $query]) : __('search.title');
        $description = __('search.searchFor', ['query' => $query ?: __('search.placeholder')]);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(request()->url());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('type', 'webpage');
    }

    public function performSearch()
    {
        $trimmedSearch = trim($this->searchInput);
        $this->errorMessage = '';
        $this->hasSearched = true;

        if (empty($trimmedSearch) || strlen($trimmedSearch) < 2) {
            $this->errorMessage = __('search.enterSearchTerm');
            $this->news = collect();
            $this->totalResults = 0;
            $this->search = '';
            $this->updateSEO('');
            return;
        }

        $this->search = $trimmedSearch;

        try {
            $query = News::query()->with('newsSite');

            // Split search term into words for more relevant results
            $searchTerms = explode(' ', $trimmedSearch);

            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty($term)) {
                        $q->where(function ($subQuery) use ($term) {
                            $subQuery->where("title->{$this->locale}", 'like', '%' . $term . '%')
                                ->orWhere("content->{$this->locale}", 'like', '%' . $term . '%');
                        });
                    }
                }
            });

            $results = $query->latest()->limit(133)->get();

            $this->news = $results;
            $this->totalResults = $results->count();
            $this->updateSEO($trimmedSearch);

        } catch (\Exception $e) {
            Log::error('Search query failed', ['error' => $e->getMessage()]);
            $this->errorMessage = __('search.error');
            $this->news = collect();
            $this->totalResults = 0;
        }
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->searchInput = '';
        $this->news = collect();
        $this->totalResults = 0;
        $this->errorMessage = '';
        $this->hasSearched = false;
        $this->updateSEO('');
    }

    public function handleKeyDown($keyCode)
    {
        if ($keyCode === 13) {
            $this->performSearch();
        }
    }
};
?>

<div class="min-h-screen  py-4 sm:py-8 ">
    <div class="container">
        <!-- Breadcrumb Navigation -->
        <nav class="text-xs sm:text-sm mb-4 sm:mb-8">
            <ul class="flex items-center space-x-1 sm:space-x-2 space-x-reverse">
                <li><a href="/" class="text-white font-medium transition">{{ __('menu.home.title') }}</a></li>
                <li class="text-gray-400">/</li>
                <li class="text-white font-medium">{{ __('search.title') }}</li>
            </ul>
        </nav>

        <!-- Header Section -->
        <div class="mb-6 sm:mb-10">
            <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1 sm:mb-2">{{ __('search.title') }}</h1>
            <p class="text-xs sm:text-base text-white">{{ __('search.searchSuggestions') }}</p>
        </div>

        <!-- Search Form -->
        <div class="mb-6 sm:mb-10">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4">
                <div class="relative flex-1 min-w-0">
                    <svg class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        wire:model="searchInput"
                        @keydown="$event.keyCode === 13 && @this.performSearch()"
                        placeholder="{{ __('search.placeholder') }}"
                        class="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-2.5 sm:py-3.5 rounded-2xl  border-2 border-gray-200 focus:ring-2   outline-none transition duration-200 bg-white shadow-lg hover:border-gray-300 text-sm text-slate-800"
                    />

                </div>

                <div class="flex gap-2 w-full sm:w-auto">
                    <button
                        wire:click="performSearch"
                        wire:loading.attr="disabled"
                        class="flex-1 sm:flex-none px-4 sm:px-6 py-2.5 sm:py-3.5 bg-blue-600 text-white rounded-xl sm:rounded-2xl hover:bg-blue-700 transition duration-200 font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                    >
                        <span wire:loading.remove wire:target="performSearch">
                           {{ __('search.title') }}
                        </span>
                        <span wire:loading wire:target="performSearch" class="flex items-center justify-center gap-2">
                             <span class="loading loading-spinner text-accent"></span>
                            درحال جستجو...
                        </span>
                    </button>

                    @if ($search)
                        <button
                            wire:click="clearSearch"
                            class="px-3 sm:px-6 py-2.5 sm:py-3.5 bg-red-500 text-white rounded-xl sm:rounded-2xl hover:bg-red-600 transition duration-200 font-medium shadow-md hover:shadow-lg text-sm sm:text-base"
                        >
                            <span class="hidden sm:inline">{{ __('search.clear') }}</span>
                            <span class="sm:hidden">پاک</span>
                        </button>
                    @endif
                </div>
            </div>

            @if ($errorMessage)
                <div class="mt-3 sm:mt-4 p-3 sm:p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <p class="text-xs sm:text-sm text-red-700 font-medium">{{ $errorMessage }}</p>
                </div>
            @endif
        </div>

        <!-- Search Results -->
        <div>
            @if ($hasSearched && $news->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 sm:py-20 text-center bg-white rounded-2xl sm:rounded-3xl shadow-lg border border-gray-100">
                    <div class="bg-blue-100 p-3 sm:p-4 rounded-full mb-3 sm:mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-12 sm:w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900 mb-1 sm:mb-2">
                        {{ __('search.noResults') }}
                    </h2>
                    <p class="text-xs sm:text-base text-white px-4">{{ __('search.searchSuggestions') }}</p>
                </div>
            @elseif ($hasSearched && $totalResults > 0)
                <div class="mb-4 sm:mb-6">
                    <div class="inline-block bg-blue-100 text-blue-800 px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium">
                        {{ __('search.totalResults', ['count' => $totalResults]) }}
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3  gap-3 sm:gap-5">
                    @foreach ($news as $item)
                            <livewire:components.news-card :news="$item" />

                    @endforeach
                </div>
            @elseif (!$hasSearched)
                <div class="flex flex-col items-center justify-center py-12 sm:py-20 text-center bg-white rounded-2xl sm:rounded-3xl shadow-lg border border-gray-100">
                    <div class="bg-gradient-to-r from-blue-100 to-purple-100 p-3 sm:p-4 rounded-full mb-3 sm:mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-12 sm:w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21l-7-5m0 0l-7 5m7-5v5m0-10l-7 5m7-5l7 5" />
                        </svg>
                    </div>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900 mb-2 sm:mb-3">
                        شروع جستجو کنید
                    </h2>
                    <p class="text-xs sm:text-base text-white px-4">کلمه یا موضوع مورد نظر خود را وارد کنید</p>
                </div>
            @endif
        </div>
    </div>
</div>
