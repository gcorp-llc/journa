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
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@32.102.2/dist/font-face.css" rel="stylesheet">
    <!-- SEO Meta Tags -->
    {!! SEO::generate(true) !!}
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
<div class="min-h-screen text-white md:pt-10 pt-15">
    <!-- Main Content -->
    <div class="container mx-auto md:px-4 md:py-8">
        {{ $slot }}
    </div>
</div>

</body>
</html>
