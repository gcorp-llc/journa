<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'خطا')</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/x-icon">

    {{-- اگر فایل‌های CSS/JS بیلد شده دارید --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- اگر از CDN استفاده می‌کنید (برای تست) --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}

    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: transparent !important; /* حیاتی برای نمایش تصویر پس‌زمینه */
        }

        /* انیمیشن شناوری برای تصویر */
        @keyframes float-in-space {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(10px, -15px) rotate(-2deg); }
            50% { transform: translate(-5px, 5px) rotate(2deg); }
            75% { transform: translate(5px, -10px) rotate(-1deg); }
        }
        .animate-float {
            animation: float-in-space 6s ease-in-out infinite;
        }

        /* انیمیشن شناوری متن */
        @keyframes text-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-text-float {
            animation: text-float 5s ease-in-out infinite;
        }

        /* افکت نئون */
        .text-neon-red { text-shadow: 0 0 10px rgba(239, 68, 68, 0.7), 0 0 20px rgba(239, 68, 68, 0.5); }
        .text-neon-yellow { text-shadow: 0 0 10px rgba(234, 179, 8, 0.7), 0 0 20px rgba(234, 179, 8, 0.5); }
        .text-neon-blue { text-shadow: 0 0 10px rgba(59, 130, 246, 0.7), 0 0 20px rgba(59, 130, 246, 0.5); }
        .text-neon-purple { text-shadow: 0 0 10px rgba(168, 85, 247, 0.7), 0 0 20px rgba(168, 85, 247, 0.5); }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col overflow-x-hidden relative text-gray-100">

{{-- 1. لایه پس‌زمینه (ثابت و تمام صفحه) --}}
<div class="fixed inset-0 z-0 w-full h-full pointer-events-none">
    {{-- تصویر پس‌زمینه --}}
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-1000"
         style="background-image: url('@yield('background-image')');">
    </div>
    {{-- لایه تاریک روی تصویر برای خوانایی بهتر متن --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px]"></div>
</div>

{{-- 2. لایه محتوا (روی پس‌زمینه) --}}
<div class="relative z-10 flex-grow flex items-center justify-center p-4 sm:p-8">
    <div class="w-full max-w-6xl mx-auto">
        <div class="flex flex-col-reverse lg:flex-row items-center justify-center gap-12 lg:gap-20 text-center lg:text-right">

            {{-- بخش متن --}}
            <div class="flex-1 space-y-6">
                <h1 class="text-8xl sm:text-9xl lg:text-[10rem] font-black tracking-tighter animate-text-float @yield('neon-color-class')">
                    @yield('code')
                </h1>

                <div class="space-y-4">
                    <h2 class="text-2xl sm:text-4xl font-bold text-white">
                        @yield('title')
                    </h2>
                    <p class="text-gray-300 text-lg sm:text-xl leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        @yield('message')
                    </p>
                </div>

                <div class="pt-8">
                    <a href="{{ url('/') }}"
                       class="inline-flex items-center justify-center px-8 py-3 text-base font-bold text-white transition-all duration-200 bg-primary hover:bg-primary-focus rounded-full shadow-lg hover:shadow-primary/40 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        بازگشت به صفحه اصلی
                    </a>
                </div>
            </div>

            {{-- بخش تصویر گرافیکی --}}
            <div class="flex-1 flex justify-center lg:justify-end">
                <div class="relative w-64 sm:w-80 lg:w-96 animate-float">
                    {{-- هاله نور پشت تصویر --}}
                    <div class="absolute inset-0 bg-white/10 blur-3xl rounded-full scale-90"></div>
                    <img src="@yield('illustration')"
                         alt="Error Illustration"
                         class="relative z-10 w-90 h-auto drop-shadow-2xl">
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
