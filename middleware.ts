import createMiddleware from 'next-intl/middleware';
import { NextRequest, NextResponse } from 'next/server';
import { routing } from './i18n/routing';

type Locale = 'en' | 'fa' | 'ar';

export async function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // منطق زبان‌ها با next-intl
  const localeFromCookie = request.cookies.get('NEXT_LOCALE')?.value as Locale | undefined;
  const localeFromPath = routing.locales.find((loc) => pathname.startsWith(`/${loc}`));
  let locale: Locale = routing.defaultLocale;

  // تعیین زبان نهایی
  if (localeFromCookie && routing.locales.includes(localeFromCookie)) {
    locale = localeFromCookie;
  } else if (localeFromPath) {
    locale = localeFromPath;
  }

  // اگر مسیر بدون پیشوند زبان است، ریدایرکت شود
  if (!localeFromPath) {
    return NextResponse.redirect(new URL(`/${locale}${pathname}`, request.url));
  }

  // اگر زبان مسیر و کوکی متفاوت است، مسیر را به زبان کوکی ریدایرکت کن
  if (localeFromCookie && localeFromPath && localeFromCookie !== localeFromPath) {
    const url = new URL(request.url);
    url.pathname = `/${localeFromCookie}${pathname.slice(localeFromPath.length + 1)}`;
    const response = NextResponse.redirect(url);
    response.cookies.set('NEXT_LOCALE', localeFromCookie, { path: '/' });
    return response;
  }

  // ست کردن کوکی زبان اگر وجود ندارد یا متفاوت است
  if (!localeFromCookie || localeFromCookie !== localeFromPath) {
    const response = createMiddleware(routing)(request);
    response.cookies.set('NEXT_LOCALE', localeFromPath || routing.defaultLocale, { path: '/' });
    return response;
  }

  // غیرفعال کردن کش
  const response = createMiddleware(routing)(request);
  response.headers.set('Cache-Control', 'no-store');
  return response;
}

export const config = {
  matcher: [
    '/((?!_next|_vercel|.*\\..*|api/).*)', // همه مسیرها به جز _next, _vercel, فایل‌ها و api
  ],
};