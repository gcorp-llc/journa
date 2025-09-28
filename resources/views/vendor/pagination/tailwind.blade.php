@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-center">
        <div class="join">
            <!-- Previous Page Button -->
            @if ($paginator->onFirstPage())
                <button class="join-item btn btn-disabled sm:inline-flex hidden" aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="join-item btn sm:inline-flex hidden" aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endif

            <!-- Pagination Elements (محدود به 5 شماره) -->
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $start = max(1, $currentPage - 2); // شروع از حداکثر 2 صفحه قبل
                $end = min($lastPage, $currentPage + 2); // پایان در حداکثر 2 صفحه بعد

                // تنظیم برای حداقل 5 شماره (اگر تعداد کل صفحات کمتر باشد)
                if ($end - $start < 4 && $lastPage > 5) {
                    $start = max(1, $end - 4);
                }
            @endphp

            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $currentPage)
                    <button class="join-item btn bg-amber-500 text-white" aria-current="page">{{ $i }}</button>
                @else
                    <a href="{{ $paginator->url($i) }}" class="join-item btn" aria-label="{{ __('Go to page :page', ['page' => $i]) }}">{{ $i }}</a>
                @endif
            @endfor

            <!-- Next Page Button -->
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="join-item btn sm:inline-flex hidden" aria-label="{{ __('pagination.next') }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            @else
                <button class="join-item btn btn-disabled sm:inline-flex hidden" aria-label="{{ __('pagination.next') }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endif
        </div>
    </nav>
@endif
