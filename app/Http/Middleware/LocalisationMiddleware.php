<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocalisationMiddleware
{
    public function handle(Request $request, Closure $next, string $lang = 'all'): Response
    {
        // اگر زبان مشخص شده باشد، از آن استفاده کن
        if ($lang !== 'all') {
            App::setLocale($lang);
            App::setFallbackLocale($lang);
            Session::put('locale', $lang);
        } else {
            // اگر Session خالی بود، فارسی پیش‌فرض باشد
            $sessionLocale = Session::get('locale', 'fa');
            App::setLocale($sessionLocale);
            App::setFallbackLocale($sessionLocale);
            Session::put('locale', $sessionLocale);
        }

        return $next($request);
    }}
