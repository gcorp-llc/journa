<?php
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use App\Models\News;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public function with(): array
    {
        return [
            'news' => News::query()
                ->orderBy('id', 'desc')           // اولویت دوم برای اطمینان
                ->paginate(177),                  // تعداد درخواستی شما
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
            @if (!$news || $news->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center border-2 border-dashed rounded-2xl bg-gray-50">
                    <p class="text-gray-500">هیچ خبری برای نمایش وجود ندارد.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($news as $item)
                        <div>
                            <livewire:components.news-card :news="$item" wire:key="news-{{ $item->id }}" />
                        </div>
                    @endforeach
                </div>

                <div class="md:py-10 py-4" dir="ltr">
                    {{ $news->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
