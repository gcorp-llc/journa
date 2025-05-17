"use client";

import { useTranslations } from "next-intl";
import { useState, useCallback } from "react";
import InfiniteScroll from "react-infinite-scroll-component";
import { debounce } from "lodash"; // اصلاح import
import NewsCard from "./NewsCard";
import Skeleton from "../ui/Skeleton";
import { useNewsStore } from "@/store/newsStore";
import { NewsItem, PaginatedNews } from "@/types/news";
import { Locale } from "@/types/common";
import { getAbsoluteUrl } from "@/lib/utils";

type Props = {
  initialNews: NewsItem[];
  locale: Locale;
  parent: string;
  initialPage: number;
  hasMore: boolean;
  total: number;
};

interface NewsApiItem {
  id: string | number;
  title: string;
  content: string;
  slug: string;
  published_at: string;
  cover: string | null;
  news_site_name: string;
  logo_url?: string;
}

export default function NewsList({
  initialNews,
  locale,
  parent,
  initialPage,
  hasMore: initialHasMore,
  total,
}: Props) {
  const t = useTranslations("News");
  const { addPaginatedNews, getPaginatedNews } = useNewsStore();
  const [newsData, setNewsData] = useState<NewsItem[]>(initialNews);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(initialPage);
  const [hasMore, setHasMore] = useState(initialHasMore);

  const filterDuplicateNews = (newNews: NewsItem[], existingNews: NewsItem[]): NewsItem[] => {
    const existingIds = new Set(existingNews.map((item) => item.id));
    return newNews.filter((item) => !existingIds.has(item.id));
  };

  const loadMoreNews = useCallback(async () => {
    if (loading || !hasMore) return;

    setLoading(true);
    try {
      const nextPage = page + 1;
      const storedNews = getPaginatedNews(locale, parent, nextPage);
      if (storedNews && storedNews.news.length > 0) {
        const uniqueNews = filterDuplicateNews(storedNews.news, newsData);
        setNewsData((prev) => [...prev, ...uniqueNews]);
        setHasMore(!!storedNews.next_page_url);
        setPage(nextPage);
        setLoading(false);
        return;
      }

      const baseUrl = process.env.NEXT_PUBLIC_BASE_URL || "http://localhost:3000";
      const param = parent.includes("/") ? "child" : "parent";
      const apiUrl = `${baseUrl}/api/news?locale=${locale}&${param}=${parent}&page=${nextPage}&perPage=33`;

      if (process.env.NODE_ENV !== "production") {
        console.log(`Loading more news from: ${apiUrl}`);
      }

      const response = await fetch(apiUrl, {
        headers: {
          "Content-Type": "application/json",
          "Accept-Language": locale,
        },
        next: { revalidate: 300 },
      });

      if (!response.ok) {
        throw new Error(`HTTP error: ${response.status}`);
      }

      const { data, next_page_url, total } = await response.json();
      if (!Array.isArray(data)) {
        throw new Error("Invalid data format");
      }

      const formattedNews: NewsItem[] = data.map((newsItem: NewsApiItem) => ({
        id: Number(newsItem.id) || 0,
        title: newsItem.title || "No title",
        content: newsItem.content || "No content",
        slug: newsItem.slug || "",
        published_at: newsItem.published_at || "",
        cover: newsItem.cover
          ? getAbsoluteUrl(
              newsItem.cover,
              process.env.NEXT_PUBLIC_BASE_IMAGE_URL || "https://core.journa.ir/storage"
            )
          : "",
        news_site_name: newsItem.news_site_name || "",
        logo_url: newsItem.logo_url
          ? getAbsoluteUrl(
              newsItem.logo_url,
              process.env.NEXT_PUBLIC_BASE_IMAGE_URL || "https://core.journa.ir/storage"
            )
          : undefined,
      }));

      const uniqueNews = filterDuplicateNews(formattedNews, newsData);
      const paginatedNews: PaginatedNews = {
        category: parent,
        page: nextPage,
        news: uniqueNews,
        next_page_url,
        total,
      };

      addPaginatedNews(locale, paginatedNews);
      setNewsData((prev) => [...prev, ...uniqueNews]);
      setHasMore(!!next_page_url);
      setPage(nextPage);
    } catch (error) {
      console.error("Error loading more news:", error);
    } finally {
      setLoading(false);
    }
  }, [loading, hasMore, page, locale, parent, addPaginatedNews, getPaginatedNews, newsData]);

  // Debounce کردن تابع loadMoreNews
  const debouncedLoadMoreNews = debounce(loadMoreNews, 300);

  return (
    <InfiniteScroll
      dataLength={newsData.length}
      next={debouncedLoadMoreNews}
      hasMore={hasMore}
      loader={<Skeleton count={6} />}
      endMessage={
        newsData.length > 0 && (
          <p className="text-center text-gray-500 col-span-full mt-4">
            {t("noMore") || "No more news to load."}
          </p>
        )
      }
    >
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        {newsData.map((item) => (
          <div key={item.id}>
            <NewsCard news={item} locale={locale} />
          </div>
        ))}
      </div>
    </InfiniteScroll>
  );
}