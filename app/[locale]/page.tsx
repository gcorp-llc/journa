import { getTranslations } from "next-intl/server";
import { notFound } from "next/navigation";
import NewsList from "./components/feed/NewsList";
import AlertError from "./components/ui/AlertError";
import { NewsItem, PaginatedNews } from "@/types/news";
import { Locale } from "@/types/common";
import { useNewsStore } from "@/store/newsStore";
import { getAbsoluteUrl } from "@/lib/utils";

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

async function fetchNews(locale: Locale, page: number): Promise<PaginatedNews> {
  const perPage = 33;
  const baseUrl = process.env.NEXT_PUBLIC_BASE_URL || "http://localhost:3000";
  const apiUrl = `${baseUrl}/api/news?locale=${locale}&page=${page}&perPage=${perPage}`;

  try {
    if (process.env.NODE_ENV !== "production") {
      console.log(`Fetching news from: ${apiUrl}`);
    }

    const res = await fetch(apiUrl, {
      headers: {
        "Content-Type": "application/json",
        "Accept-Language": locale,
      },
      // simulate revalidate header
      next: { revalidate: 300 },
    });

    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }

    const { data, next_page_url, total } = await res.json();

    if (!Array.isArray(data)) {
      console.error("Invalid data format: data is not an array", data);
      throw new Error("Invalid data format");
    }

    const seenIds = new Set<number | string>();
    const formattedNews: NewsItem[] = data
      .filter((newsItem: NewsApiItem) => {
        const isValid = newsItem.id && newsItem.title && newsItem.content;
        const isDuplicate = seenIds.has(newsItem.id);
        seenIds.add(newsItem.id);
        if (!isValid) return false;
        if (isDuplicate) return false;
        return true;
      })
      .map((newsItem: NewsApiItem) => ({
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

    const validatedNextPageUrl =
      next_page_url && typeof next_page_url === "string" && next_page_url.includes("page=")
        ? next_page_url
        : null;

    return {
      category: "latest",
      page,
      news: formattedNews,
      next_page_url: validatedNextPageUrl,
      total,
    };
  } catch (error) {
    console.error("Error fetching news:", error);
    throw error;
  }
}

type Props = {
  params: Promise<{ locale: Locale }>;
  searchParams: Promise<{ page?: string }>;
};

export default async function HomePage({
  params: paramsPromise,
  searchParams: searchParamsPromise,
}: Props) {
  const { locale } = await paramsPromise;
  const { page: pageStr = "1" } = await searchParamsPromise;
  const page = Math.max(1, parseInt(pageStr) || 1);

  const tNews = await getTranslations("News");
  const tMenu = await getTranslations("menu");

  if (!["en", "fa", "ar"].includes(locale)) {
    console.error(`Invalid locale: ${locale}`);
    notFound();
  }

  const pageTitle = tMenu("home.title") || "Home";
  const pageDescription = tMenu("home.description") || "Latest news and updates";

  const existingNews = useNewsStore.getState().getPaginatedNews(locale, "latest", page);
  let newsData: PaginatedNews;

  if (existingNews) {
    newsData = existingNews;
  } else {
    try {
      newsData = await fetchNews(locale, page);
      useNewsStore.getState().addPaginatedNews(locale, newsData);
    } catch (error) {
      return (
        <div className="container mx-auto py-8">
          <h1 className="text-2xl font-bold mb-4">{pageTitle}</h1>
          <p className="text-lg text-gray-600 mb-4">{pageDescription}</p>
          <AlertError message={tNews("error") || "خطایی در بارگذاری اخبار رخ داد"} />
        </div>
      );
    }
  }

  if (newsData.news.length === 0) {
    return (
      <div className="container mx-auto py-8 text-center">
        <h1 className="text-2xl font-bold mb-4">{pageTitle}</h1>
        <p className="text-lg text-gray-600 mb-4">{pageDescription}</p>
        <p className="text-lg text-gray-500">{tNews("noNews") || "هیچ اخباری یافت نشد."}</p>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-8">
      <h1 className="text-2xl font-bold mb-4">{pageTitle}</h1>
      <p className="text-lg text-gray-600 mb-4">{pageDescription}</p>
      <NewsList
        initialNews={newsData.news}
        locale={locale}
        parent=""
        initialPage={page}
        hasMore={!!newsData.next_page_url}
        total={newsData.total}
      />
    </div>
  );
}

export const dynamic = "force-dynamic";
