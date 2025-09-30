<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="dark">
<head>
    {{-- بخش head بدون تغییر باقی می‌ماند --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'خطا')</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{asset('favicon.png')}}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* انیمیشن شناوری برای تصویر */
        @keyframes float-in-space {
            0% { transform: translate(0px, 0px) rotate(0deg); }
            25% { transform: translate(5px, -15px) rotate(-2deg); }
            50% { transform: translate(-5px, 0px) rotate(2deg); }
            75% { transform: translate(5px, -10px) rotate(-1deg); }
            100% { transform: translate(0px, 0px) rotate(0deg); }
        }
        .animate-float {
            animation: float-in-space 12s ease-in-out infinite;
        }

        /* (جدید) انیمیشن شناوری مجزا و آرام برای متن کد خطا */
        @keyframes text-float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-text-float {
            animation: text-float 8s ease-in-out infinite;
        }

        /* (تغییر) انیمیشن‌های درخشش نئون با جزئیات و رنگ‌بندی جدید */
        @keyframes neon-pulse-yellow {
            0%, 100% { text-shadow: 0 0 7px rgba(225, 232, 32, 0.7), 0 0 10px rgba(198, 198, 8, 0.7), 0 0 21px rgba(227, 230, 54, 0.7), 0 0 42px #b39c0bd0, 0 0 82px #f0db4f, 0 0 92px #f0db4f; }
            50% { text-shadow: 0 0 10px rgba(225, 211, 7, 0.9), 0 0 20px rgba(217, 169, 25, 0.9), 0 0 31px rgba(236, 215, 24, 0.9), 0 0 52px #f0db4f, 0 0 92px #f0db4f, 0 0 102px #f0db4f; }
        }
        @keyframes neon-pulse-red {
            0%, 100% { text-shadow: 0 0 7px rgba(239, 68, 68, 0.7), 0 0 10px rgba(220, 38, 38, 0.7), 0 0 21px rgba(244, 67, 54, 0.7), 0 0 42px #ef4444, 0 0 82px #ef4444, 0 0 92px #ef4444; }
            50% { text-shadow: 0 0 10px rgba(244, 67, 54, 0.9), 0 0 20px rgba(239, 83, 80, 0.9), 0 0 31px rgba(229, 57, 53, 0.9), 0 0 52px #ef4444, 0 0 92px #ef4444, 0 0 102px #ef4444; }
        }
        @keyframes neon-pulse-blue {
            0%, 100% { text-shadow: 0 0 7px rgba(59, 130, 246, 0.7), 0 0 10px rgba(37, 99, 235, 0.7), 0 0 21px rgba(29, 78, 216, 0.7), 0 0 42px #3b82f6, 0 0 82px #3b82f6, 0 0 92px #3b82f6; }
            50% { text-shadow: 0 0 10px rgba(30, 136, 229, 0.9), 0 0 20px rgba(33, 150, 243, 0.9), 0 0 31px rgba(66, 165, 245, 0.9), 0 0 52px #3b82f6, 0 0 92px #3b82f6, 0 0 102px #3b82f6; }
        }
        /* (جدید) انیمیشن درخشش برای رنگ بنفش */
        @keyframes neon-pulse-purple {
            0%, 100% { text-shadow: 0 0 7px rgba(139, 92, 246, 0.7), 0 0 10px rgba(124, 58, 237, 0.7), 0 0 21px rgba(109, 40, 217, 0.7), 0 0 42px #8b5cf6, 0 0 82px #8b5cf6, 0 0 92px #8b5cf6; }
            50% { text-shadow: 0 0 10px rgba(167, 139, 250, 0.9), 0 0 20px rgba(147, 112, 219, 0.9), 0 0 31px rgba(139, 92, 246, 0.9), 0 0 52px #8b5cf6, 0 0 92px #8b5cf6, 0 0 102px #8b5cf6; }
        }

        /* استایل‌های نئون با انیمیشن درخشش */
        .text-neon-yellow { color: #f0db4f; animation: neon-pulse-yellow 2.5s infinite ease-in-out; }
        .text-neon-red { color: #ef4444; animation: neon-pulse-red 2.5s infinite ease-in-out; }
        .text-neon-blue { color: #3b82f6; animation: neon-pulse-blue 2.5s infinite ease-in-out; }
        .text-neon-purple { color: #8b5cf6; animation: neon-pulse-purple 2.5s infinite ease-in-out; } /* (جدید) */

    </style>
</head>
<body class="font-sans relative">

    <div
        class="absolute inset-0 w-full h-full bg-cover bg-center -z-10"
        style="background-image: url('@yield('background-image')');">
        <div class="absolute inset-0 w-full h-full bg-black/70"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center p-4 overflow-hidden">
        <div class="
            flex flex-col-reverse items-center text-center gap-y-8
            md:flex-row md:text-right md:gap-y-0 md:gap-x-12 md:max-w-4xl
        ">

            <div class="space-y-4 md:flex-1">
                {{-- (تغییر) انیمیشن شناوری به کد خطا اضافه شد --}}
                <h1 class="text-7xl md:text-9xl font-black animate-text-float @yield('neon-color-class')">
                    @yield('code')
                </h1>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-100">
                    @yield('title')
                </h2>
                <p class="max-w-md mx-auto text-gray-300 md:mx-0 italic tracking-wide">
                    @yield('message')
                </p>
                <div class="mt-8">
                    <a href="{{ url('/') }}" class="btn btn-primary btn-wide shadow-lg shadow-primary/30">بازگشت به خانه</a>
                </div>
            </div>

            <div class="md:flex-shrink-0">
                <img src="@yield('illustration')" alt="Illustration" class="w-32 md:w-52 animate-float mx-auto">
            </div>
            
        </div>
    </div>

</body>
</html>

