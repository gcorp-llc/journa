<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use WithPagination;

    public $query = '';
    public $news = [];
    public $total = 0;
    public $currentPage = 1;
    public $lastPage = 1;
    public $perPage = 33;
    public $errorMessage = '';
    public $locale;

    public function mount()
    {
        // Set locale based on the current URL segment
        $this->locale = app()->getLocale();

        // Initialize query from URL parameter
        $this->query = request()->query('query', '');

        // Set SEO metadata
        $title = __('search.title');
        $description = __('search.resultsTitle', ['query' => $this->query ?: __('search.placeholder')]);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(request()->url());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('type', 'webpage');

        SEOMeta::addMeta('twitter:card', 'summary_large_image');
        SEOMeta::addMeta('twitter:title', $title);
        SEOMeta::addMeta('twitter:description', $description);

        // Perform initial search if query exists
        if ($this->query) {
            $this->search();
        }
    }

    public function updatingQuery($value)
    {
        // Reset pagination and error message when query changes
        $this->resetPage();
        $this->errorMessage = '';
    }

    public function search()
    {
        if (empty($this->query)) {
            $this->errorMessage = __('search.enterSearchTerm');
            $this->news = [];
            $this->total = 0;
            return;
        }

        try {
            $response = Http::get(route('api.search'), [
                'query' => $this->query,
                'locale' => $this->locale,
                'perPage' => $this->perPage,
                'page' => $this->currentPage,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->news = $data['data'];
                $this->total = $data['total'];
                $this->currentPage = $data['current_page'];
                $this->lastPage = $data['last_page'];
                $this->errorMessage = $data['message'] ?? '';
            } else {
                $this->errorMessage = __('search.error');
                $this->news = [];
                $this->total = 0;
                Log::error('Search API failed', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            $this->errorMessage = __('search.error');
            $this->news = [];
            $this->total = 0;
            Log::error('Search API exception', ['error' => $e->getMessage()]);
        }
    }

    public function setPage($page)
    {
        $this->currentPage = $page;
        $this->search();
    }

    public function with()
    {
        return [
            'news' => $this->news,
            'total' => $this->total,
            'currentPage' => $this->currentPage,
            'lastPage' => $this->lastPage,
        ];
    }


};
?>

<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumbs text-sm mb-6">
        <ul class="flex items-center space-x-2 space-x-reverse">
            <li>
                <a href="/" class="text-blue-600 hover:underline">{{ __('menu.home.title') }}</a>
            </li>
            <li class="text-gray-400">/</li>
            <li class="text-gray-600 font-medium">{{ __('search.title') }}</li>
        </ul>
    </nav>

    <!-- Page Title -->
    <h1 class="text-4xl font-bold text-gray-800 mb-3">{{ __('search.title') }}</h1>
    <p class="text-lg text-gray-600 mb-8">{{ __('search.resultsTitle', ['query' => $query ?: __('search.placeholder')]) }}</p>

    <!-- Search Form -->
    <div class="mb-10">
        <div class="flex items-center space-x-4 space-x-reverse">
            <input
                type="text"
                wire:model.live.debounce.500ms="query"
                placeholder="{{ __('search.placeholder') }}"
                class="w-full max-w-lg px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200"
            >
            <button
                wire:click="search"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200"
            >
                {{ __('search.button') }}
            </button>
            @if ($query)
                <button
                    wire:click="$set('query', '')"
                    class="px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200"
                >
                    {{ __('search.clear') }}
                </button>
            @endif
        </div>
        @if ($errorMessage)
            <p class="mt-3 text-red-600">{{ $errorMessage }}</p>
        @endif
    </div>

    <!-- Search Results -->
    <div class="mt-10">
        @if (empty($news) && !$errorMessage)
            <div class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed rounded-xl bg-gray-50 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h2 class="text-2xl font-semibold text-gray-700 mb-2">{{ __('search.noResults') }}</h2>
                <p class="text-gray-500 max-w-md">{{ __('search.searchSuggestions') }}</p>
            </div>
        @else
            <div class="mb-6">
                <p class="text-gray-600">
                    {{ $total === 1 ? __('search.totalResultsSingular') : __('search.totalResults', ['count' => $total]) }}
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($news as $item)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                        <livewire:components.news-card :news="$item" />
                    </div>
                @endforeach
            </div>
            @if ($total > 0)
                <div class="mt-12">
                    <nav class="flex justify-center">
                        <ul class="flex items-center space-x-2 space-x-reverse">
                            @if ($currentPage > 1)
                                <li>
                                    <button wire:click="setPage({{ $currentPage - 1 }})" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                                        {{ __('pagination.previous') }}
                                    </button>
                                </li>
                            @endif
                            @for ($i = 1; $i <= $lastPage; $i++)
                                <li>
                                    <button wire:click="setPage({{ $i }})" class="{{ $currentPage === $i ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} px-4 py-2 rounded-lg hover:bg-gray-300 transition duration-200">
                                        {{ $i }}
                                    </button>
                                </li>
                            @endfor
                            @if ($currentPage < $lastPage)
                                <li>
                                    <button wire:click="setPage({{ $currentPage + 1 }})" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                                        {{ __('pagination.next') }}
                                    </button>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @endif
        @endif
    </div>
</div>

