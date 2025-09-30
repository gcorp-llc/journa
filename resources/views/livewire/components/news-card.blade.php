<a href="{{ url(app()->getLocale() . '/news/' . $news->slug) }}" class="block" aria-label="{{ $news->title }}">
    <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 rounded-xl overflow-hidden">
        <div class="relative w-full h-48 bg-gray-100">
            <img
                src="{{ Storage::url($news->cover) ?? asset('placeholder.png') }}"
                alt="{{ $news->title }}"
                class="object-cover w-full h-full transition-transform duration-500 hover:scale-105"
                loading="lazy"
                onerror="this.src='{{ asset('placeholder.png') }}'"
            />

            <div class="absolute bottom-2 right-2 badge badge-soft badge-secondary text-xs font-medium">
                @php
                    $locale = app()->getLocale();
                    if ($locale == 'fa') {
                        $date = \Morilog\Jalali\Jalalian::fromDateTime($news->published_at)->format('Y/m/d');
                    } elseif ($locale == 'ar') {
                        $date = (new DateTime($news->published_at))->format('Y/m/d');
                    } else {
                        $date = $news->published_at->format('Y/m/d');
                    }
                @endphp
                {{ $date }}
            </div>
            <div class="absolute bottom-2 left-2 badge badge-soft badge-primary text-xs font-medium">
                {{ $news->newsSite->name }}
            </div>
        </div>

        <div class="card-body p-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="card-title text-lg font-semibold line-clamp-2">
                    {{ $news->title }}
                </h2>
            </div>

            <p class="text-gray-600  line-clamp-4">
                {{ Str::limit(strip_tags($news->content),333) }}
            </p>
        </div>
    </div>
</a>
