<!DOCTYPE html>
<html data-theme="nord" lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Vazir Font -->
    {{--    <link href="https://cdn.jsdelivr.net/npm/vazir-font@32.102.2/dist/font-face.css" rel="stylesheet">--}}
    <!-- SEO Meta Tags -->
    {!! SEO::generate() !!}
    <!-- Google Tag Manager -->
    <script>
        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5CZP6X6N');
    </script>
    <!-- End Google Tag Manager -->
    {{--    <link--}}
    {{--        rel="stylesheet"--}}
    {{--        href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"--}}
    {{--    />--}}

</head>
<body data-theme="nord" class="font-vazir outline-black/5 bg-gray-900/90">
<!-- Google Tag Manager (noscript) -->
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5CZP6X6N" height="0" width="0"
            style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->
<!-- Header Section -->
<livewire:components.header/>
<header class="relative overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-gradient-to-b from-neutral to-black opacity-95"></div>

    <!-- Subtle Pattern -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.05),transparent_60%)]"></div>

    <!-- Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-6 py-24 text-center">


        <!-- Title -->
        <h1 class="text-3xl md:text-5xl font-bold  tracking-wide leading-relaxed text-white">
            به یاد فرزندان جاویدان این سرزمین
        </h1>

        <!-- Divider -->
        <div class="flex justify-center my-8">
            <span class="h-px w-32 bg-error opacity-60"></span>
        </div>

        <!-- Subtitle (optional) -->
        <p class="max-w-2xl mx-auto text-base md:text-lg text-white leading-loose">
            یادشان همواره در قلب این خاک زنده خواهد ماند
        </p>
    </div>
</header>
<div class="min-h-screen text-white md:pt-10 pt-15 bg-slate-800">
    <!-- Main Content -->
    <div class="container mx-auto md:px-4 md:py-8">
        {{ $slot }}
    </div>
</div>

</body>
</html>
