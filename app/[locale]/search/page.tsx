'use client';

import { useState, useEffect, useCallback, Suspense, use } from 'react';
import InfiniteScroll from 'react-infinite-scroll-component';
import { useTranslations, useLocale } from 'next-intl';
import NewsCard from '../components/feed/NewsCard';
import Skeleton from '../components/ui/Skeleton';
import AlertError from '../components/ui/AlertError';
import SearchBox from '../components/feed/SearchBox';
import { Locale } from '@/types/common';

type NewsItem = {
  id: number;
  title: string;
  content: string;
  slug: string;
  published_at: string;
  image?: string;
  category?: string;
};

type ApiResponse = {
  data: NewsItem[];
  next_page_url: string | null;
  total: number;
  message?: string;
};

async function fetchSearchResults(
  query: string,
  page: number,
  locale: string
): Promise<ApiResponse> {
  console.log('Fetching search results:', { query, page, locale });
  const response = await fetch(
    `/api/search?query=${encodeURIComponent(query)}&page=${page}&locale=${locale}&perPage=33`,
    { cache: 'no-store' }
  );
  if (!response.ok) {
    let errorData;
    try {
      errorData = await response.json();
    } catch {
      errorData = {};
    }
    throw new Error(errorData?.message || errorData?.error || 'Failed to fetch search results');
  }
  const data = await response.json();
  console.log('API response:', {
    dataLength: data.data?.length,
    total: data.total,
    nextPageUrl: data.next_page_url,
    currentPage: page,
  });
  return data;
}

const LoadingState = () => (
  <div className="w-full">
    <Skeleton count={6} />
  </div>
);

type SearchParams = {
  query?: string;
};

type PageProps = {
  params: Promise<{ locale: Locale }>;
  searchParams: Promise<SearchParams>;
};

export default function SearchPage({ params, searchParams }: PageProps) {
  const { locale } = use(params);
  const resolvedSearchParams = use(searchParams);
  const t = useTranslations('Search');
  const currentLocale = useLocale();
  const query = resolvedSearchParams.query?.trim() || '';
  const [news, setNews] = useState<NewsItem[]>([]);
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [hasMore, setHasMore] = useState(true);
  const [error, setError] = useState<Error | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  console.log('SearchPage rendered:', { query, locale, searchParams: resolvedSearchParams });

  const loadResults = useCallback(
    async (reset = false) => {
      if (!query) {
        setIsLoading(false);
        setError(null);
        setNews([]);
        setTotal(0);
        setHasMore(false);
        console.log('No query provided, resetting state');
        return;
      }

      if (isLoading) {
        console.log('Search skipped: Already loading');
        return;
      }

      setIsLoading(true);
      const currentPage = reset ? 1 : page;
      console.log('Starting search load:', { query, page: currentPage, reset });

      try {
        const response = await fetchSearchResults(query, currentPage, currentLocale);

        const uniqueNews = response.data.filter(
          (item, index, self) => self.findIndex((i) => i.id === item.id) === index
        );
        console.log('Unique news items:', { count: uniqueNews.length, page: currentPage });

        setNews((prev) => {
          const newNews = reset ? uniqueNews : [...prev, ...uniqueNews];
          const finalNews = Array.from(
            new Map(newNews.map((item) => [item.id, item])).values()
          );
          console.log('Updated news:', { totalLength: finalNews.length, total: response.total });
          return finalNews;
        });

        setTotal(response.total);
        const loadedCount = (reset ? 0 : news.length) + uniqueNews.length;
        const newHasMore = loadedCount < response.total && uniqueNews.length > 0;
        setHasMore(newHasMore);
        console.log('Has more:', {
          loadedCount,
          total: response.total,
          hasMore: newHasMore,
          nextPageUrl: response.next_page_url,
        });

        setPage((prev) => (reset ? 2 : prev + 1));
        setError(null);
      } catch (err) {
        console.error('Error fetching search results:', err);
        setError(err instanceof Error ? err : new Error(t('error')));
      } finally {
        setIsLoading(false);
        console.log('Search load completed:', { query, page: currentPage, newsLength: news.length });
      }
    },
    [query, page, currentLocale, t, isLoading, news.length]
  );

  useEffect(() => {
    console.log('useEffect triggered:', { query, locale });
    setPage(1);
    setNews([]);
    setTotal(0);
    setHasMore(true);
    setError(null);
    setIsLoading(true);
    loadResults(true);

    return () => {
      console.log('Cleaning up useEffect');
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [query, locale]);

  if (error) {
    return (
      <div className="container mx-auto p-4 font-vazir">
        <SearchBox locale={locale} className="mb-6" />
        <AlertError message={error.message} />
      </div>
    );
  }

  return (
    <Suspense fallback={<LoadingState />}>
      <div className="container mx-auto p-4 font-vazir">
        <div className="mb-6 w-full">
          <SearchBox locale={locale} />
        </div>

        {query ? (
          <>
            <h2 className="mb-4 text-xl flex justify-between">
              {t('resultsTitle', { query })}
              {total > 0 && (
                <span className="badge badge-soft badge-success">
                  {total === 1 ? t('totalResultsSingular') : t('totalResults', { count: total })}
                </span>
              )}
            </h2>
            <InfiniteScroll
              dataLength={news.length}
              next={() => {
                console.log('InfiniteScroll next triggered:', { page, hasMore });
                loadResults();
              }}
              hasMore={hasMore}
              loader={<Skeleton count={3} />}
              endMessage={
                news.length > 0 ? (
                  <div className="text-center py-4">
                    {t('noMore')}
                  </div>
                ) : null
              }
            >
              {isLoading && news.length === 0 ? (
                <Skeleton count={6} />
              ) : news.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {news.map((item) => (
                    <div key={item.id}>
                      <NewsCard news={item} locale={locale} />
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-4">
                  {t('noResults')}
                  <p className="text-gray-500 mt-2">{t('searchSuggestions')}</p>
                </div>
              )}
            </InfiniteScroll>
          </>
        ) : (
          <div className="text-center py-8">
            <p className="text-gray-600">{t('enterSearchTerm')}</p>
          </div>
        )}
      </div>
    </Suspense>
  );
}