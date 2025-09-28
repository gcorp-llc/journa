@php
    use Carbon\Carbon;

    $formatDate = function ($dateString) use ($locale) {
        if (!$dateString) {
            return __('news.noDate');
        }
        try {
            $carbonDate = Carbon::parse($dateString);
            $localeMap = [
                'fa' => 'fa_IR',
                'ar' => 'ar_EG',
                'en' => 'en_US'
            ];
            return $carbonDate->locale($localeMap[$locale])->isoFormat('D MMMM YYYY');
        } catch (\Exception $e) {
            return __('news.invalidDate');
        }
    };

    $cleanContent = function ($content) {
        $content = strip_tags($content);
        return Str::limit($content, 190, '...');
    };

    $imageSrc = $news['cover'] ?: asset('placeholder.png');
@endphp

<a href="{{ app()->getLocale(), '/news/' . $news['slug'] }}"
   class="block"
   aria-label="{{ $news['title'] }}">
    <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 rounded-xl overflow-hidden">
        <div class="relative w-full h-48 bg-gray-100">
            <img src="{{ $imageSrc }}"
                 alt="{{ $news['title'] }}"
                 class="object-cover w-full h-full transition-transform duration-500 hover:scale-105"
                 loading="lazy"
                 onerror="this.src='{{ asset('placeholder.png') }}'" />
        </div>
        <div class="card-body p-4">
            <div class="flex items-center justify-between mb-2 {{ in_array($locale, ['fa', 'ar']) ? 'flex-row-reverse' : '' }}">
                <h2 class="card-title text-lg font-semibold line-clamp-2">
                    {{ $news['title'] }}
                </h2>
                @if ($news['news_site_name'])
                    <span class="text-xs text-gray-500">
                        {{ $news['news_site_name'] }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-400 mb-2 {{ in_array($locale, ['fa', 'ar']) ? 'text-right' : '' }}">
                {{ $formatDate($news['published_at']) }}
            </p>
            <p class="text-gray-600 text-sm line-clamp-2 {{ in_array($locale, ['fa', 'ar']) ? 'text-right' : '' }}">
                {{ $cleanContent($news['content']) }}
            </p>
        </div>
    </div>
</a>

