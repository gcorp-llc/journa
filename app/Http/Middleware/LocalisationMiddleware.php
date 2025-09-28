<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocalisationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $lang = null): Response
    {
        // 1. اگر توی URL prefix زبان مشخص شده بود
        if ($lang && in_array($lang, ['fa', 'en', 'ar'])) {
            $locale = $lang;
        }
        // 2. اگر توی Session زبان ذخیره شده بود
        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
        }
        // 3. پیش‌فرض
        else {
            $locale = 'fa';
        }

        // تنظیم زبان
        App::setLocale($locale);
        App::setFallbackLocale('fa');

        // ذخیره در Session
        Session::put('locale', $locale);

        return $next($request);
    }
}
