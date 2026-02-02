<?php
use Livewire\Component;
use App\Models\News;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public $search = ''; // بایند کردن به URL برای حفظ وضعیت هنگام رفرش

    public $locale;

    // برای جلوگیری از ارسال درخواست‌های زیاد هنگام تایپ
    public $searchInput = '';

    public function mount()
    {
        $this->locale = app()->getLocale();
        $this->searchInput = $this->search;
        $this->updateSEO();
    }

    // وقتی دکمه جستجو زده شد این متد صدا زده می‌شود
    public function applySearch()
    {
        $this->search = trim($this->searchInput);
        $this->resetPage(); // بازگشت به صفحه اول هنگام جستجوی جدید
        $this->updateSEO();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->searchInput = '';
        $this->resetPage();
        $this->updateSEO();
    }

    public function updateSEO()
    {
        $title = $this->search ? __('search.resultsTitle', ['query' => $this->search]) : __('search.title');
        $description = __('search.searchFor', ['query' => $this->search ?: __('search.placeholder')]);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(request()->url());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(request()->url());
    }

    public function with()
    {
        $news = collect(); // کالکشن خالی پیش‌فرض

        if (strlen($this->search) >= 2) {
            try {
                $query = News::query()->with('newsSite');

                // جدا کردن کلمات برای جستجوی دقیق‌تر
                $searchTerms = explode(' ', $this->search);

                $query->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        if (!empty($term)) {
                            $q->where(function ($subQuery) use ($term) {
                                // جستجو در عنوان و محتوا بر اساس زبان
                                $subQuery->where("title->{$this->locale}", 'like', '%' . $term . '%')
                                    ->orWhere("content->{$this->locale}", 'like', '%' . $term . '%');
                            });
                        }
                    }
                });

                // استفاده از Paginate به جای Get
                // و مرتب‌سازی نزولی برای نمایش اخبار جدید در ابتدا
                $news = $query->orderBy('published_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->paginate(177);

            } catch (\Exception $e) {
                Log::error('Search query failed', ['error' => $e->getMessage()]);
            }
        } else {
            // اگر جستجو خالی بود یا کوتاه بود، یک صفحه خالی با صفحه بندی فیک برگردانیم یا null
            // در اینجا من null برمی‌گردانم تا در ویو هندل شود
            $news = null;
        }

        return [
            'news' => $news,
        ];
    }
};
?>

<div class="min-h-screen py-4 sm:py-8">
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

                    <!-- ورودی جستجو -->
                    <input
                        type="text"
                        wire:model="searchInput"
                        wire:keydown.enter="applySearch"
                        placeholder="{{ __('search.placeholder') }}"
                        class="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-2.5 sm:py-3.5 rounded-2xl border-2 border-gray-200 focus:ring-2 outline-none transition duration-200 bg-white shadow-lg hover:border-gray-300 text-sm text-slate-800"
                    />
                </div>

                <div class="flex gap-2 w-full sm:w-auto">
                    <button
                        wire:click="applySearch"
                        class="flex-1 sm:flex-none px-4 sm:px-6 py-2.5 sm:py-3.5 bg-blue-600 text-white rounded-xl sm:rounded-2xl hover:bg-blue-700 transition duration-200 font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                    >
                        <span wire:loading.remove wire:target="applySearch">
                           {{ __('search.title') }}
                        </span>
                        <span wire:loading wire:target="applySearch" class="flex items-center justify-center gap-2">
                             <span class="loading loading-spinner text-accent"></span>
                            ...
                        </span>
                    </button>

                    @if ($search)
                        <button
                            wire:click="clearSearch"
                            class="px-3 sm:px-6 py-2.5 sm:py-3.5 bg-red-500 text-white rounded-xl sm:rounded-2xl hover:bg-red-600 transition duration-200 font-medium shadow-md hover:shadow-lg text-sm sm:text-base"
                        >
                            <span class="hidden sm:inline">{{ __('search.clear') }}</span>
                            <span class="sm:hidden">X</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div>
            @if ($news && $news->count() > 0)
                <div class="mb-4 sm:mb-6">
                    <div class="inline-block bg-blue-100 text-blue-800 px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium">
                        {{ __('search.totalResults', ['count' => $news->total()]) }}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5">
                    @foreach ($news as $item)
                        <livewire:components.news-card :news="$item" wire:key="search-news-{{ $item->id }}" />
                    @endforeach
                </div>

                <div class="md:py-10 py-4" dir="ltr">
                    {{ $news->links() }}
                </div>

            @elseif ($search && $news && $news->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 sm:py-20 text-center bg-white rounded-2xl sm:rounded-3xl shadow-lg border border-gray-100">
                    <div class="bg-blue-100 p-3 sm:p-4 rounded-full mb-3 sm:mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-12 sm:w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900 mb-1 sm:mb-2">
                        {{ __('search.noResults') }}
                    </h2>
                    <p class="text-xs sm:text-base text-gray-500 px-4">{{ __('search.searchSuggestions') }}</p>
                </div>

            @else
                <div class="flex flex-col items-center justify-center py-12 sm:py-20 text-center bg-white rounded-2xl sm:rounded-3xl shadow-lg border border-gray-100">
                    <div class="bg-gradient-to-r from-blue-100 to-purple-100 p-3 sm:p-4 rounded-full mb-3 sm:mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-12 sm:w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21l-7-5m0 0l-7 5m7-5v5m0-10l-7 5m7-5l7 5" />
                        </svg>
                    </div>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900 mb-2 sm:mb-3">
                        شروع جستجو کنید
                    </h2>
                    <p class="text-xs sm:text-base text-gray-500 px-4">کلمه یا موضوع مورد نظر خود را وارد کنید</p>
                </div>
            @endif
        </div>
    </div>
</div>
