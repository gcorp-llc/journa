<?php

use Livewire\Component;
use Livewire\WithPagination;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use App\Models\Category;

new class extends Component {
    use WithPagination;

    public $category;
    public $childCategory;

    public function mount($category_slug, $child_slug)
    {
        $this->category = Category::where('slug', $category_slug)->firstOrFail();

        $this->childCategory = Category::where('slug', $child_slug)
            ->where('parent_id', $this->category->id)
            ->firstOrFail();

        SEOMeta::setTitle($this->childCategory->title);
        SEOMeta::setDescription($this->childCategory->description);
        SEOMeta::setCanonical(request()->url());

        OpenGraph::setTitle($this->childCategory->title);
        OpenGraph::setDescription($this->childCategory->description);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('type', 'webpage');

        SEOMeta::addMeta('twitter:card', 'summary_large_image');
        SEOMeta::addMeta('twitter:title', $this->childCategory->title);
        SEOMeta::addMeta('twitter:description', $this->childCategory->description);
    }

    public function with()
    {
        return [
            'news' => $this->childCategory->news()
                ->orderBy('id', 'desc')
                ->paginate(77),
        ];
    }
};
?>

<div class="py-5 text-white">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="/">{{ __('menu.home.title') }}</a></li>
            <li><a href="{{ '/cat/' . $category->slug }}">{{ $category->title}}</a></li>
            <li>{{ $childCategory->title}}</li>
        </ul>
    </div>

    <!-- Page Title and Description -->
    <h1 class="text-4xl font-bold text-white mb-3">{{$childCategory->title }}</h1>
    <p class="text-lg text-white text-justify leading-relaxed">
        {{$childCategory->description }}
    </p>

    <!-- News Section -->
    <div class="mt-8">
        @if ($news->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center border-2 border-dashed rounded-2xl bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17v-2a4 4 0 014-4h4m0 0V7a4 4 0 00-4-4H7a4 4 0 00-4 4v6a4 4 0 004 4h2m6-6l6 6"/>
                </svg>
                <h2 class="text-xl font-semibold text-gray-600 mb-2">{{ __('news.noContent') }}</h2>
                <p class="text-gray-500">{{ __('news.noContent') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($news as $item)
                    <div>
                        <livewire:components.news-card :news="$item" wire:key="child-news-{{ $item->id }}" />
                    </div>
                @endforeach
            </div>
            <div class="md:py-10 py-4" dir="ltr">
                {{ $news->links() }}
            </div>
        @endif
    </div>
</div>
