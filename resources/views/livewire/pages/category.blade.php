<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use WithPagination;

    public $category;
    public $title;
    public $description;

    public function mount($slug)
    {
        // Find the category
        $this->category = Category::where('slug', $slug)->firstOrFail();

        // Set title and description with fallback for missing translations
        $this->title = __("menu.{$this->category->slug}.title") ?: $this->category->slug;
        $this->description = __("menu.{$this->category->slug}.description") ?: 'No description available';

        // Log missing translations
        if (__("menu.{$this->category->slug}.title") === "menu.{$this->category->slug}.title") {
            Log::warning("Translation missing for menu.{$this->category->slug}.title in locale " . app()->getLocale());
        }

        // Set SEO metadata
        SEOMeta::setTitle($this->title);
        SEOMeta::setDescription($this->description);
        SEOMeta::setCanonical(request()->url());

        OpenGraph::setTitle($this->title);
        OpenGraph::setDescription($this->description);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('type', 'webpage');

        SEOMeta::addMeta('twitter:card', 'summary_large_image');
        SEOMeta::addMeta('twitter:title', $this->title);
        SEOMeta::addMeta('twitter:description', $this->description);
    }

    public function with()
    {
        return [
            'news' => $this->category->news()->paginate(66),
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
            <li class="text-gray-600 font-medium">
                {{ __('menu.' . $category->slug . '.title') }}
                @if (__('menu.' . $category->slug . '.title') === 'menu.' . $category->slug . '.title')
                    <span class="text-red-500 text-xs">[Translation Missing]</span>
                @endif
            </li>
        </ul>
    </nav>

    <!-- Page Title and Description -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-3">
            {{ __('menu.' . $category->slug . '.title') }}
            @if (__('menu.' . $category->slug . '.title') === 'menu.' . $category->slug . '.title')
                <span class="text-red-500 text-sm">[Translation Missing]</span>
            @endif
        </h1>
        <p class="text-lg text-gray-600 leading-relaxed">
            {{ __('menu.' . $category->slug . '.description') }}
            @if (__('menu.' . $category->slug . '.description') === 'menu.' . $category->slug . '.description')
                <span class="text-red-500 text-sm">[Translation Missing]</span>
            @endif
        </p>
    </div>

    <!-- News Section -->
    <div class="mt-10">
        @if ($news->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed rounded-xl bg-gray-50 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17v-2a4 4 0 014-4h4m0 0V7a4 4 0 00-4-4H7a4 4 0 00-4 4v6a4 4 0 004 4h2m6-6l6 6"/>
                </svg>
                <h2 class="text-2xl font-semibold text-gray-700 mb-2">{{ __('news.noContent') }}</h2>
                <p class="text-gray-500 max-w-md">{{ __('news.noContent') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($news as $item)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                        <livewire:components.news-card :news="$item" />
                    </div>
                @endforeach
            </div>
            <div class="mt-12">
                {{ $news->links() }}
            </div>
        @endif
    </div>
</div>

