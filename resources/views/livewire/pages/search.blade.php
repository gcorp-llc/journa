<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\News;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    #[Url]
    public $search = '';
    public $errorMessage = '';
    public $locale;

    public function mount()
    {
        $this->locale = app()->getLocale();

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

    #[Computed]
    public function news()
    {
        // Trim search input
        $trimmedSearch = trim($this->search);

        // Update SEO dynamically based on the search query
        $this->updateSEO($trimmedSearch);

        // Return empty collection if search is too short
        if (strlen($trimmedSearch) < 2) {
            // Return an empty paginator to avoid errors in the view
            return new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 33);
        }

        try {
            $query = News::query()->with('newsSite');

            // [IMPROVEMENT] Split search term into words for more relevant results
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

            return $query->latest()->paginate(33);

        } catch (\Exception $e) {
            Log::error('Search query failed', ['error' => $e->getMessage()]);
            // Return an empty paginator on error
            return new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 33);
        }
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
    public function updatingSearch($value)
    {
        $this->resetPage();
        $this->errorMessage = '';
        if (strlen($value) < 2 && $value !== '') {
            $this->errorMessage = __('search.enterSearchTerm');
        }
    }

    public function with()
    {
        if (empty(trim($this->search)) || strlen($this->search) < 2) {
            return [
                'news' => collect(),
                'errorMessage' => $this->errorMessage ?: ($this->search ? __('search.enterSearchTerm') : ''),
            ];
        }

        try {
            $query = News::query()
                ->with('newsSite') // Load newsSite relation
                ->where(function ($query) {
                    $locale = $this->locale;
                    $query->where("title->$locale", 'like', '%' . $this->search . '%')
                        ->orWhere("content->$locale", 'like', '%' . $this->search . '%');
                })->latest();

            $results = $query->paginate(33); // Reduced for better UX

            return [
                'news' => $results,
                'errorMessage' => $this->errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('Search query failed', ['error' => $e->getMessage()]);
            $this->errorMessage = __('search.error');
            return [
                'news' => collect(),
                'errorMessage' => $this->errorMessage,
            ];
        }
    }
};
?>

<div class="container mx-auto px-4 py-6 max-w-7xl" >
    <!-- Breadcrumb Navigation -->
    <nav class="text-sm mb-4">
        <ul class="flex items-center space-x-2 space-x-reverse">
            <li><a href="/" class="text-blue-500 hover:underline">{{ __('menu.home.title') }}</a></li>
            <li class="text-gray-500">/</li>
            <li class="text-gray-700 font-medium">{{ __('search.title') }}</li>
        </ul>
    </nav>

    <!-- Search Form -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <div class="relative w-full max-w-md">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('search.placeholder') }}"
                    class="w-full px-4 py-2.5 rounded-full border border-gray-200 focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none transition duration-150 bg-white shadow-sm"
                />
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-3 flex items-center">
                    <svg class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8h8a8 8 0 01-8 8 8 8 0 01-8-8z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex gap-2">
                @if ($search)
                    <button
                        wire:click="$set('search', '')"
                        class="px-4 py-2.5 bg-gray-100 text-gray-600 rounded-full hover:bg-gray-200 transition duration-150"
                    >
                        {{ __('search.clear') }}
                    </button>
                @endif
            </div>
        </div>
        @if ($errorMessage)
            <p class="mt-2 text-sm text-red-500">{{ $errorMessage }}</p>
        @endif
    </div>

    <!-- Search Results -->
    <div>
        @if ($news->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center bg-gray-50 rounded-lg shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h2 class="text-xl font-medium text-gray-600">
                    {{ __('search.noResults') }}
             </h2>
                <p class="text-gray-500 mt-1">{{ __('search.searchSuggestions') }}</p>
            </div>
        @else
            <div class="mb-4 text-sm text-gray-600">
                {{ __('search.totalResults', ['count' => $news->total()]) }}
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($news as $item)
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                        <livewire:components.news-card :news="$item" />
                    </div>
                @endforeach
            </div>
            @if ($news->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $news->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
