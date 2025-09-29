@php
    if (!isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
           (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth' })
        JS
        : '';

    // اضافه کردن پیشوند زبان به URL‌ها
    $locale = app()->getLocale();
    // ایجاد عناصر پگینیشن
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $maxPages = 5; // تعداد صفحات ثابت برای همه دستگاه‌ها
    $halfMax = floor($maxPages / 2);
    $start = max(1, $currentPage - $halfMax);
    $end = min($lastPage, $currentPage + $halfMax);

    // تنظیم محدوده برای تعداد صفحات ثابت
    if ($end - $start < $maxPages - 1 && $lastPage > $maxPages) {
        $start = max(1, $end - ($maxPages - 1));
    }

    $elements = [];

    // صفحه اول
    if ($start > 1) {
        $elements[] = ['page' => 1, 'url' => $paginator->url(1)];
        if ($start > 2) {
            $elements[] = '...';
        }
    }

    // صفحات میانی
    for ($i = $start; $i <= $end; $i++) {
        $elements[] = ['page' => $i, 'url' => $paginator->url($i)];
    }

    // صفحه آخر و سه‌نقطه
    if ($end < $lastPage) {
        if ($end < $lastPage - 1) {
            $elements[] = '...';
        }
        $elements[] = ['page' => $lastPage, 'url' => $paginator->url($lastPage)];
    }
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
         class="flex flex-col sm:flex-row items-center justify-between gap-4 sm:gap-6 bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-4 sm:p-6 w-full max-w-3xl mx-auto transition-all duration-300">
        <!-- Showing Results -->
        <div class="text-center sm:text-right hidden md:block">
            <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-300 font-medium tracking-tight">

                <span class="font-bold">{{ $paginator->firstItem() }}</span>
                {{ __('to') }}
                <span class="font-bold">{{ $paginator->lastItem() }}</span>
                {{ __('of') }}
                <span class="font-bold">{{ $paginator->total() }}</span>

            </p>
        </div>

        <!-- Pagination Links -->
        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
            <!-- Previous Page Link -->
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-full cursor-not-allowed select-none transition-all duration-200">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5l-7 7 7 7"/>
                    </svg>

                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                   class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-teal-600 border border-transparent rounded-full hover:from-emerald-600 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-emerald-300 dark:focus:ring-teal-800 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                   aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5l-7 7 7 7"/>
                    </svg>

                </a>
            @endif

            <!-- Pagination Elements -->
            @foreach ($elements as $element)
                <!-- Ellipsis -->
                @if (is_string($element))
                    <span class="inline-flex items-center px-3 py-2 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 bg-transparent cursor-default select-none">
                        {{ $element }}
                    </span>
                @endif

                <!-- Page Links -->
                @if (is_array($element))
                    @if ($element['page'] == $paginator->currentPage())
                        <span aria-current="page"
                              class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-700 border border-emerald-600 rounded-full cursor-default select-none shadow-md">
                            {{ $element['page'] }}
                        </span>
                    @else
                        <a href="{{ $element['url'] }}" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                           class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-emerald-600 dark:text-teal-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-full hover:bg-emerald-50 dark:hover:bg-gray-700 hover:text-emerald-800 dark:hover:text-teal-200 focus:outline-none focus:ring-2 focus:ring-emerald-300 dark:focus:ring-teal-800 transition-all duration-300 hover:shadow-md transform hover:-translate-y-0.5"
                           aria-label="{{ __('Go to page :page', ['page' => $element['page']]) }}">
                            {{ $element['page'] }}
                        </a>
                    @endif
                @endif
            @endforeach

            <!-- Next Page Link -->
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                   class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-teal-600 border border-transparent rounded-full hover:from-emerald-600 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-emerald-300 dark:focus:ring-teal-800 transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                   aria-label="{{ __('pagination.next') }}">

                    <svg class="w-4 h-4 sm:w-5 sm:h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-full cursor-not-allowed select-none transition-all duration-200">

                    <svg class="w-4 h-4 sm:w-5 sm:h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
