<?php
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use App\Models\News;

new class extends Component {
    use WithPagination,WithoutUrlPagination;

    public function with(): array
    {
        return [
            'newsItems' =>  News::where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->paginate(33),
        ];
    }
    public function mount()
    {
        // تنظیمات SEO Meta
        SEOMeta::setTitle(__('news.title'));
        SEOMeta::setDescription(__('metadata.description'));
        SEOMeta::setCanonical(request()->url());

        // تنظیمات OpenGraph
        OpenGraph::setTitle(__('news.title'));
        OpenGraph::setDescription(__('metadata.description'));
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('type', 'webpage');

        // تنظیمات Twitter Card
        SEOMeta::addMeta('twitter:card', 'summary_large_image');
        SEOMeta::addMeta('twitter:title', __('news.title'));
        SEOMeta::addMeta('twitter:description', __('metadata.description'));
    }
} ?>


<div>
    <div class="py-5">
        <h1 class="text-3xl font-bold">{{ __('menu.home.title') }}</h1>
        <p>{{ __('menu.home.description') }}</p>

        <div class="mt-8">
            @if (!$newsItems || $newsItems->isEmpty())
                <p class="text-gray-500">هیچ خبری برای نمایش وجود ندارد.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($newsItems as $item)
                        <div>
                            <livewire:components.news-card :news="$item" />
                        </div>
                    @endforeach
                </div>

                <div class="py-10">
                    {{ $newsItems->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
