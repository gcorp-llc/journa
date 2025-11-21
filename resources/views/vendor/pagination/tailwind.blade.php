@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-center py-8">
        <div class="flex flex-wrap items-center justify-center gap-2 p-2 bg-white/5 backdrop-blur-sm rounded-full shadow-sm border border-white/10">

            {{-- دکمه قبلی --}}
            @if ($paginator->onFirstPage())
                <span class="flex items-center justify-center w-10 h-10 rounded-full bg-white/10 text-white/30 cursor-not-allowed">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" wire:navigate class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-500 text-white hover:bg-amber-600 transition-all duration-200 shadow-md hover:shadow-amber-500/30" aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- المان‌های صفحه‌بندی --}}
            <div class="hidden sm:flex gap-2">
                @foreach ($elements as $element)
                    {{-- جداکننده سه نقطه --}}
                    @if (is_string($element))
                        <span class="flex items-center justify-center w-10 h-10 text-gray-400 font-medium">{{ $element }}</span>
                    @endif

                    {{-- آرایه لینک‌ها --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white font-bold shadow-lg scale-110 cursor-default ring-2 ring-white/20" aria-current="page">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" wire:navigate class="flex items-center justify-center w-10 h-10 rounded-full bg-white/10 text-white hover:bg-amber-500 hover:text-white transition-all duration-200 font-medium" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- حالت موبایل: نمایش ساده‌تر --}}
            <div class="sm:hidden flex items-center text-white text-sm font-medium px-2">
                صفحه {{ $paginator->currentPage() }} از {{ $paginator->lastPage() }}
            </div>

            {{-- دکمه بعدی --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" wire:navigate class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-500 text-white hover:bg-amber-600 transition-all duration-200 shadow-md hover:shadow-amber-500/30" aria-label="{{ __('pagination.next') }}">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span class="flex items-center justify-center w-10 h-10 rounded-full bg-white/10 text-white/30 cursor-not-allowed">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
