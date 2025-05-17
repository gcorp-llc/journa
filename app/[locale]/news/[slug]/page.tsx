import { getTranslations } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { NewsItem } from '@/types/news';
import { Locale } from '@/types/common';
import { useNewsStore } from '@/store/newsStore';
import { getAbsoluteUrl } from '@/lib/utils';
import NewsContentClient from '../../components/feed/NewsContentClient';
import AlertError from '../../components/ui/AlertError';

function generateDescription(content: string, maxLength: number = 160): string {
  const slicedContent = content.slice(0, 500);
  const plainText = slicedContent
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
  return plainText.length > maxLength ? `${plainText.substring(0, maxLength - 3)}...` : plainText;
}

function generateKeywords(title: string, newsSiteName?: string): string {
  const titleWords = title
    .split(' ')
    .filter((word) => word.length > 3)
    .slice(0, 5)
    .map((word) => word.toLowerCase());
  const keywords = [...new Set([...titleWords, newsSiteName?.toLowerCase() || 'news'])];
  return keywords.join(', ');
}

async function fetchNews(locale: Locale, slug: string): Promise<NewsItem> {
  const baseUrl = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:3000';
  // اضافه کردن views و source_url به فیلدها
  const apiUrl = `${baseUrl}/api/news/${slug}?locale=${locale}&fields=id,title,content,slug,published_at,cover,news_site_name,logo_url,views,source_url`;

  try {
    const response = await fetch(apiUrl, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept-Language': locale,
      },
      next: { revalidate: 300 },
    });

    if (!response.ok) {
      throw new Error(`API error: ${response.status}`);
    }

    const { data } = await response.json();
    if (!data) {
      throw new Error('No data returned from API');
    }

    const newsItem: NewsItem = {
      id: Number(data.id) || 0,
      title: data.title || 'No title',
      content: data.content || 'No content',
      slug: data.slug || '',
      published_at: data.published_at || new Date().toISOString(),
      cover: data.cover
        ? getAbsoluteUrl(data.cover, process.env.NEXT_PUBLIC_BASE_IMAGE_URL || 'https://core.journa.ir/storage')
        : '/placeholder.webp',
      news_site_name: data.news_site_name || '',
      logo_url: data.logo_url
        ? getAbsoluteUrl(data.logo_url, process.env.NEXT_PUBLIC_BASE_IMAGE_URL || 'https://core.journa.ir/storage')
        : undefined,
      views: data.views !== undefined ? Number(data.views) : undefined,
      source_url: data.source_url || undefined,
    };

    useNewsStore.getState().setSingleNews(locale, newsItem);
    return newsItem;
  } catch (error) {
    console.error('Error fetching news:', error);
    throw error;
  }
}

export async function generateMetadata({
  params: paramsPromise,
}: {
  params: Promise<{ locale: Locale; slug: string }>;
}) {
  const { locale, slug } = await paramsPromise;
  const baseUrl = process.env.NEXT_PUBLIC_BASE_URL || 'https://journa.ir';
  const t = await getTranslations('News');

  let news = useNewsStore.getState().getSingleNews(locale, slug);
  if (!news) {
    try {
      news = await fetchNews(locale, slug);
    } catch (error) {
      return {
        title: `${t('notFound')} | Journa`,
        description: t('articleNotFound') || 'مقاله خبری مورد نظر یافت نشد.',
      };
    }
  }

  const pageTitle = `${news.title} | ${news.news_site_name || 'Journa'}`;
  const pageDescription = generateDescription(news.content);
  const pageKeywords = generateKeywords(news.title, news.news_site_name);
  const canonicalUrl = news.source_url || `${baseUrl}/${locale}/news/${news.slug}`;
  const ogImage = news.cover || `${baseUrl}/favicon.webp`;

  return {
    title: pageTitle,
    description: pageDescription,
    keywords: pageKeywords,
    robots: 'index, follow',
    alternates: {
      canonical: canonicalUrl,
      languages: {
        fa: `${baseUrl}/fa/news/${news.slug}`,
        en: `${baseUrl}/en/news/${news.slug}`,
        ar: `${baseUrl}/ar/news/${news.slug}`,
        'x-default': `${baseUrl}/en/news/${news.slug}`,
      },
    },
    openGraph: {
      title: pageTitle,
      description: pageDescription,
      type: 'article',
      url: canonicalUrl,
      images: [
        {
          url: ogImage,
          alt: news.title,
          width: 1200,
          height: 630,
        },
      ],
      locale: locale === 'fa' ? 'fa_IR' : locale === 'ar' ? 'ar_AR' : 'en_US',
      siteName: 'Journa',
      article: {
        publishedTime: news.published_at,
        modifiedTime: news.published_at,
      },
    },
    twitter: {
      card: 'summary_large_image',
      title: pageTitle,
      description: pageDescription,
      images: [{ url: ogImage, alt: news.title }],
      site: '@Journa',
    },
    other: {
      'script:ld+json': JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'NewsArticle',
        headline: news.title,
        image: news.cover ? [news.cover] : [],
        datePublished: news.published_at,
        dateModified: news.published_at,
        author: {
          '@type': 'Organization',
          name: news.news_site_name || 'Journa',
        },
        publisher: {
          '@type': 'Organization',
          name: 'Journa',
          logo: {
            '@type': 'ImageObject',
            url: `${baseUrl}/favicon.webp`,
          },
        },
        description: pageDescription,
        mainEntityOfPage: {
          '@type': 'WebPage',
          '@id': canonicalUrl,
        },
        interactionCount: news.views ? `UserPageVisits:${news.views}` : undefined,
        breadcrumb: {
          '@type': 'BreadcrumbList',
          itemListElement: [
            {
              '@type': 'ListItem',
              position: 1,
              name: 'Home',
              item: baseUrl,
            },
            {
              '@type': 'ListItem',
              position: 2,
              name: 'News',
              item: `${baseUrl}/${locale}/news`,
            },
            {
              '@type': 'ListItem',
              position: 3,
              name: news.title,
              item: canonicalUrl,
            },
          ],
        },
      }),
    },
  };
}

export default async function NewsContentPage({
  params: paramsPromise,
}: {
  params: Promise<{ locale: Locale; slug: string }>;
}) {
  const { locale, slug } = await paramsPromise;
  const t = await getTranslations('News');

  if (!['en', 'fa', 'ar'].includes(locale) || !slug) {
    notFound();
  }

  let news: NewsItem;
  try {
    const storedNews = useNewsStore.getState().getSingleNews(locale, slug);
    if (storedNews) {
      news = storedNews;
    } else {
      news = await fetchNews(locale, slug);
    }
  } catch (error) {
    console.error('Error fetching news:', error);
    return (
      <div className="container mx-auto p-4">
        <AlertError message={t('error') || 'خطا در بارگذاری خبر'} />
      </div>
    );
  }

  return (
  <NewsContentClient
    news={{
      title: news.title,
      content: news.content,
      coverImageUrl: news.cover || '/placeholder.webp',
      published_at: news.published_at,
      news_site_name: news.news_site_name,
      category_slug: 'news',
      locale,
      views: news.views,
      source_url: news.source_url,
      slug: news.slug, // slug به درستی ارسال شده است
    }}
    locale={locale}
  />
  );
}

export const dynamic = 'force-static';
export const revalidate = 300;