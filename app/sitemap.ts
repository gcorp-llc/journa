import type { MetadataRoute } from 'next';
import { NewsItem } from '@/types/news';
import { Locale } from '@/types/common';

const BASE_URL = process.env.NEXT_PUBLIC_BASE_URL || 'https://journa.ir';
const ALLOWED_LOCALES: Locale[] = ['en', 'fa', 'ar'];

// تابع برای دریافت اخبار از API
async function fetchNews(locale: Locale): Promise<NewsItem[]> {
  try {
    const apiUrl = `${process.env.BASE_URL || 'https://core.journa.ir'}/news?locale=${locale}&fields=id,slug,published_at`;
    const response = await fetch(apiUrl, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept-Language': locale,
      },
      cache: 'no-store',
    });

    if (!response.ok) {
      console.error(`Failed to fetch news for locale ${locale}: ${response.statusText}`);
      return [];
    }

    const { data } = await response.json();
    return Array.isArray(data) ? data : [];
  } catch (error) {
    console.error(`Error fetching news for locale ${locale}:`, error);
    return [];
  }
}

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  try {
    // دریافت اخبار برای هر زبان
    const newsItems: Record<Locale, NewsItem[]> = {
      en: [],
      fa: [],
      ar: [],
    };

    for (const locale of ALLOWED_LOCALES) {
      newsItems[locale] = await fetchNews(locale);
    }

    // تولید URLهای سایت‌مپ
    const urls: MetadataRoute.Sitemap = [];

    // اضافه کردن صفحه اصلی برای هر زبان
    ALLOWED_LOCALES.forEach((locale) => {
      urls.push({
        url: `${BASE_URL}/${locale}`,
        changeFrequency: 'daily',
        priority: 1.0,
        alternates: {
          languages: Object.fromEntries(
            ALLOWED_LOCALES.map((lang) => [lang, `${BASE_URL}/${lang}`])
          ),
        },
      });
    });

    // اضافه کردن URLهای اخبار
    ALLOWED_LOCALES.forEach((locale) => {
      newsItems[locale].forEach((news) => {
        urls.push({
          url: `${BASE_URL}/${locale}/news/${news.slug}`,
          lastModified: news.published_at ? new Date(news.published_at) : undefined,
          changeFrequency: 'daily',
          priority: 0.8,
          alternates: {
            languages: Object.fromEntries(
              ALLOWED_LOCALES.map((lang) => [lang, `${BASE_URL}/${lang}/news/${news.slug}`])
            ),
          },
        });
      });
    });

    return urls;
  } catch (error) {
    console.error('Error generating sitemap:', error);
    return [];
  }
}