'use client';

import { useTranslations } from 'next-intl';
import { FC, memo, useEffect } from 'react';
import { useParams } from 'next/navigation';
import DOMPurify from 'isomorphic-dompurify';
import NewsImage from '../feed/NewsImage';
import { Locale } from '@/types/common';
import Head from 'next/head';

interface NewsContentClientProps {
  news: {
    id?: number;
    title: string;
    content: string;
    coverImageUrl?: string;
    published_at?: string;
    news_site_name?: string;
    category_slug?: string;
    locale: Locale;
    views?: number;
    source_url?: string;
    slug: string; // اضافه کردن slug به‌عنوان ویژگی اجباری
  };
  locale: Locale;
}

const NewsContentClient: FC<NewsContentClientProps> = ({ news, locale }) => {
  const t = useTranslations('News');
  const params = useParams();
  const effectiveLocale = locale || (params.locale as Locale) || 'en';

  // افزایش بازدید خبر در زمان لود صفحه
  useEffect(() => {
    const incrementViews = async () => {
      try {
        const response = await fetch(`/api/news/${news.slug}/increment-views?locale=${effectiveLocale}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
        });
        if (!response.ok) {
          console.error('Failed to increment views:', response.statusText);
        }
      } catch (error) {
        console.error('Error incrementing views:', error);
      }
    };

    incrementViews();
  }, [news.slug, effectiveLocale]);

  const placeholderImage = '/placeholder.png';
  const imageSrc = news.coverImageUrl || placeholderImage;

  const formatDate = (dateString?: string): string => {
    if (!dateString) return t('noDate') || 'No date available';
    try {
      return new Date(dateString).toLocaleDateString(
        effectiveLocale === 'fa' ? 'fa-IR' : effectiveLocale === 'ar' ? 'ar-EG' : 'en-US',
        {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
        }
      );
    } catch (error) {
      console.error('Error formatting date:', error);
      return t('invalidDate') || 'Invalid date';
    }
  };

  const sanitizedContent = DOMPurify.sanitize(news.content, {
    ALLOWED_TAGS: ['p', 'b', 'i', 'strong', 'em', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'br'],
    ALLOWED_ATTR: ['href', 'target', 'rel'],
  });

  const formattedDate = formatDate(news.published_at);

  const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'NewsArticle',
    headline: news.title,
    datePublished: news.published_at || new Date().toISOString(),
    image: [imageSrc.startsWith('http') ? imageSrc : `${process.env.NEXT_PUBLIC_BASE_URL || 'https://journa.ir'}${imageSrc}`],
    author: {
      '@type': 'Organization',
      name: news.news_site_name || 'Journa',
    },
    publisher: {
      '@type': 'Organization',
      name: 'Journa',
      logo: {
        '@type': 'ImageObject',
        url: `${process.env.NEXT_PUBLIC_BASE_URL || 'https://journa.ir'}/favicon.jpg`,
      },
    },
    articleSection: news.category_slug || 'General',
    inLanguage: effectiveLocale === 'fa' ? 'fa-IR' : effectiveLocale === 'ar' ? 'ar-AR' : 'en-US',
    interactionCount: news.views ? `UserPageVisits:${news.views}` : undefined,
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': news.source_url || `${process.env.NEXT_PUBLIC_BASE_URL || 'https://journa.ir'}/${effectiveLocale}/news/${news.slug}`,
    },
  };

  return (
    <>
      <Head>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }}
        />
      </Head>

      <div className="relative container mx-auto p-4 sm:p-6 md:mt-5">
        <div
          className="absolute rounded-3xl glass top-0 left-0 w-full h-[70vh] sm:h-[60vh] bg-gradient-to-b from-amber-600 to-transparent z-10 pointer-events-none"
          style={{
            maskImage: 'linear-gradient(to bottom, black 40%, transparent 70%)',
            WebkitMaskImage: 'linear-gradient(to bottom, black 40%, transparent 70%)',
          }}
        ></div>

        <article
          className={`prose prose-lg max-w-3xl mx-auto relative z-20 ${
            effectiveLocale === 'fa' || effectiveLocale === 'ar' ? 'rtl' : 'ltr'
          }`}
        >
          <h1 className="text-3xl font-bold mb-4 leading-tight text-white drop-shadow-md">
            {news.title}
          </h1>
          <div className="flex items-center justify-between text-sm text-white mb-6 drop-shadow-sm">
            <div className="flex items-center gap-2">
              {news.news_site_name && (
                <span className="font-medium">{news.news_site_name} |</span>
              )}
              <time dateTime={news.published_at}>{formattedDate}</time>
            </div>
            <div className="flex items-center gap-2">
              {news.views !== undefined && (
                <span>{t('views', { count: news.views })}</span>
              )}
              {news.source_url && (
                <a
                  href={news.source_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-blue-400 hover:underline"
                >
                  {t('source')}
                </a>
              )}
            </div>
          </div>
          <NewsImage
            src={imageSrc}
            alt={news.title}
            isCover
            className="rounded-lg shadow-sm mb-6 w-full"
            loading="lazy"
           
          />
          {sanitizedContent ? (
            <div
              className="content text-justify leading-relaxed text-gray-700 text-lg"
              dangerouslySetInnerHTML={{ __html: sanitizedContent }}
            />
          ) : (
            <p className="text-gray-800 text-2xl">{t('noContent') || 'No content available'}</p>
          )}
        </article>
      </div>
    </>
  );
};

export default memo(NewsContentClient);