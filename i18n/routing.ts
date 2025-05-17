import { defineRouting } from 'next-intl/routing';

export const routing = defineRouting({
  locales: ['fa', 'en', 'ar'],
  defaultLocale: 'fa',
  localePrefix: 'always', // همیشه پیشوند زبان در URL باشد (مثل /fa)
  localeDetection: true, // تشخیص خودکار زبان مرورگر غیرفعال است
});