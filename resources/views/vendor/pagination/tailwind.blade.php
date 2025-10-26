@if ($paginator->hasPages())

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-center py-4">
        <div class="flex items-center gap-2">
            <!-- Previous Page Button -->
            @if ($paginator->onFirstPage())
                <button class="btn btn-sm sm:btn-md bg-amber-500 text-white cursor-not-allowed rounded-full p-2 sm:p-3 transition-all" disabled aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" wire:navigate class="btn btn-sm sm:btn-md bg-amber-500 hover:bg-amber-600 text-white rounded-full p-2 sm:p-3 transition-all" aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            <!-- Pagination Elements -->
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $isMobile = request()->isMobile() ?? false; // فرض می‌کنیم متد isMobile وجود دارد
                $maxPages = $isMobile ? 3 : 7; // 3 صفحه برای موبایل، 7 برای دسکتاپ
                $halfMax = floor($maxPages / 2);
                $start = max(1, $currentPage - $halfMax);
                $end = min($lastPage, $currentPage + $halfMax);

                // تنظیم برای حداقل تعداد صفحات
                if ($end - $start < $maxPages - 1 && $lastPage > $maxPages) {
                    $start = max(1, $end - ($maxPages - 1));
                }
            @endphp

                <!-- First Page -->
            @if ($start > 1)
                <a href="{{ $paginator->url(1) }}" wire:navigate class="btn btn-sm sm:btn-md bg-amber-500 hover:bg-amber-600 text-white rounded-full px-3 sm:px-4 transition-all" aria-label="{{ __('Go to page 1') }}">1</a>
                @if ($start > 2)
                    <span class="text-gray-500 px-2">...</span>
                @endif
            @endif

            <!-- Page Numbers -->
            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $currentPage)
                    <button class="btn btn-sm sm:btn-md bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-full px-3 sm:px-4 shadow-md" aria-current="page">{{ $i }}</button>
                @else
                    <a href="{{ $paginator->url($i) }}" wire:navigate class="btn btn-sm sm:btn-md bg-amber-500 hover:bg-amber-600 text-white rounded-full px-3 sm:px-4 transition-all" aria-label="{{ __('Go to page :page', ['page' => $i]) }}">{{ $i }}</a>
                @endif
            @endfor

            <!-- Last Page -->
            @if ($end < $lastPage)
                @if ($end < $lastPage - 1)
                    <span class="text-gray-500 px-2">...</span>
                @endif
                <a href="{{ $paginator->url($lastPage) }}" wire:navigate class="btn btn-sm sm:btn-md bg-amber-500 hover:bg-amber-600 text-white rounded-full px-3 sm:px-4 transition-all" aria-label="{{ __('Go to page :page', ['page' => $lastPage]) }}">{{ $lastPage }}</a>
            @endif

            <!-- Next Page Button -->
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" wire:navigate class="btn btn-sm sm:btn-md bg-amber-500 hover:bg-amber-600 text-white rounded-full p-2 sm:p-3 transition-all" aria-label="{{ __('pagination.next') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <button class="btn btn-sm sm:btn-md bg-gray-200 text-gray-500 cursor-not-allowed rounded-full p-2 sm:p-3 transition-all" disabled aria-label="{{ __('pagination.next') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @endif
        </div>
    </nav>
@endif
