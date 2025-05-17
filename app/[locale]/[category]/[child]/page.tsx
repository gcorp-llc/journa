import { getTranslations } from "next-intl/server";
import { notFound } from "next/navigation";
import NewsList from "../../components/feed/NewsList";
import Breadcrumb from "../../components/feed/Breadcrumb";
import AlertError from "../../components/ui/AlertError";
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

interface MenuChild {
  title: string;
  url: string;
  description?: string;
}

async function fetchChildNews(locale: Locale, child: string, page: number): Promise<PaginatedNews> {
  const perPage = 33;
  const baseUrl = process.env.NEXT_PUBLIC_BASE_URL || "http://localhost:3000";
  const apiUrl = `${baseUrl}/api/news?locale=${locale}&child=${child}&page=${page}&perPage=${perPage}`;

  try {
    if (process.env.NODE_ENV !== "production") {
      console.log(`Fetching child news from: ${apiUrl}`);
    }
    
    const response = await fetch(apiUrl, {
      method: 'GET',
      headers: {
        "Content-Type": "application/json",
        "Accept-Language": locale,
      },
      next: { revalidate: 300 }, // کش برای 5 دقیقه
    });

    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const responseData = await response.json();
    const { data, next_page_url, total } = responseData;
    
    if (!Array.isArray(data)) {
      throw new Error("Invalid data format");
    }

    const seenIds = new Set<number | string>();
    const formattedNews: NewsItem[] = data
      .filter((newsItem: NewsApiItem) => {
        const isValid = newsItem.id && newsItem.title && newsItem.content;
        const isDuplicate = seenIds.has(newsItem.id);
        seenIds.add(newsItem.id);
        if (!isValid) {
          if (process.env.NODE_ENV !== "production") {
            console.warn("Invalid news item filtered:", newsItem);
          }
          return false;
        }
        if (isDuplicate) {
          if (process.env.NODE_ENV !== "production") {
            console.warn("Duplicate news item filtered:", newsItem);
          }
          return false;
        }
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

    if (process.env.NODE_ENV !== "production") {
      console.log(`Formatted child news: count=${formattedNews.length}`);
    }

    return {
      category: child,
      page,
      news: formattedNews,
      next_page_url: next_page_url && typeof next_page_url === "string" ? next_page_url : null,
      total,
    };
  } catch (error) {
    console.error("Error fetching child news:", error);
    throw error;
  }
}

export async function generateMetadata({
  params: paramsPromise,
}: {
  params: Promise<{ locale: Locale; category: string; child: string }>;
}) {
  const { locale, category, child } = await paramsPromise;
  const baseUrl = process.env.NEXT_PUBLIC_BASE_URL || "https://journa.ir";
  const tMenu = await getTranslations("menu");

  const childExists = tMenu
    .raw(`${category}.children`)
    ?.some((c: { url: string }) => c.url === child || c.url.endsWith(`/${child}`));
  if (!tMenu.raw(category) || !tMenu(`${category}.title`) || !childExists) {
    return {
      title: `${tMenu("notFound")} | Journa`,
      description: tMenu("missingCategory") || "Subcategory not found.",
    };
  }

  const children = tMenu.raw(`${category}.children`) as MenuChild[] | undefined;
  const childItem = children?.find(
    (c) => c.url === child || c.url === `${category}/${child}` || c.url.endsWith(`/${child}`)
  );

  const childTitle = childItem?.title || child.replace(/-/g, " ");
  const pageTitle = `${childTitle} | Journa`;
  const pageDescription = childItem?.description || `Latest news from ${childTitle}`;

  return {
    title: pageTitle,
    description: pageDescription,
    keywords: `${childTitle}, ${category}, news, journa`,
    robots: "index, follow",
    alternates: {
      canonical: `${baseUrl}/${locale}/${category}/${child}`,
      languages: {
        fa: `${baseUrl}/fa/${category}/${child}`,
        en: `${baseUrl}/en/${category}/${child}`,
        ar: `${baseUrl}/ar/${category}/${child}`,
        "x-default": `${baseUrl}/en/${category}/${child}`,
      },
    },
    openGraph: {
      title: pageTitle,
      description: pageDescription,
      type: "website",
      url: `${baseUrl}/${locale}/${category}/${child}`,
      images: [{ url: `${baseUrl}/favicon.jpg`, alt: childTitle }],
      locale: locale === "fa" ? "fa_IR" : locale === "ar" ? "ar_AR" : "en_US",
      siteName: "Journa",
    },
    twitter: {
      card: "summary_large_image",
      title: pageTitle,
      description: pageDescription,
      images: [{ url: `${baseUrl}/favicon.jpg`, alt: childTitle }],
      site: "@Journa",
    },
    other: {
      "script:ld+json": JSON.stringify({
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        name: childTitle,
        description: pageDescription,
        url: `${baseUrl}/${locale}/${category}/${child}`,
        breadcrumb: {
          "@type": "BreadcrumbList",
          itemListElement: [
            {
              "@type": "ListItem",
              position: 1,
              name: tMenu("home.title") || "Home",
              item: baseUrl,
            },
            {
              "@type": "ListItem",
              position: 2,
              name: tMenu(`${category}.title`) || category.replace(/-/g, " "),
              item: `${baseUrl}/${locale}/${category}`,
            },
            {
              "@type": "ListItem",
              position: 3,
              name: childTitle,
              item: `${baseUrl}/${locale}/${category}/${child}`,
            },
          ],
        },
      }),
    },
  };
}

type Props = {
  params: Promise<{ locale: Locale; category: string; child: string }>;
  searchParams: Promise<{ page?: string }>;
};

export default async function CategoryChildPage({
  params: paramsPromise,
  searchParams: searchParamsPromise,
}: Props) {
  const { locale, category, child } = await paramsPromise;
  const { page: pageStr = "1" } = await searchParamsPromise;
  const page = Math.max(1, parseInt(pageStr) || 1);

  const tNews = await getTranslations("News");
  const tMenu = await getTranslations("menu");

  const childExists = tMenu
    .raw(`${category}.children`)
    ?.some((c: { url: string }) => c.url === child || c.url.endsWith(`/${child}`));
  if (!["en", "fa", "ar"].includes(locale) || !tMenu.raw(category) || !tMenu(`${category}.title`) || !childExists) {
    if (process.env.NODE_ENV !== "production") {
      console.log(`Invalid access: locale=${locale}, category=${category}, child=${child}`);
    }
    notFound();
  }

  let newsData: PaginatedNews;
  try {
    const storedNews = useNewsStore.getState().getPaginatedNews(locale, child, page);
    if (storedNews) {
      if (process.env.NODE_ENV !== "production") {
        console.log(`Using cached news: locale=${locale}, child=${child}, page=${page}`);
      }
      newsData = storedNews;
    } else {
      newsData = await fetchChildNews(locale, child, page);
      useNewsStore.getState().addPaginatedNews(locale, newsData);
      if (process.env.NODE_ENV !== "production") {
        console.log(
          `Stored news: locale=${locale}, child=${child}, page=${page}, count=${
            newsData.news.length
          }`
        );
      }
    }
  } catch (error) {
    console.error("Child category page error:", error);
    return (
      <div className="container mx-auto p-4">
        <Breadcrumb parent={category} child={child} locale={locale} />
        <AlertError message={tNews("error") || "Error loading news."} />
      </div>
    );
  }

  if (newsData.news.length === 0) {
    return (
      <div className="container mx-auto p-4">
        <Breadcrumb parent={category} child={child} locale={locale} />
        <p className="text-lg text-gray-500">
          {tNews("noContent") || "No news found."}
        </p>
      </div>
    );
  }

  const children = tMenu.raw(`${category}.children`) as MenuChild[] | undefined;
  const childItem = children?.find(
    (c) => c.url === child || c.url === `${category}/${child}` || c.url.endsWith(`/${child}`)
  );
  const childTitle = childItem?.title || child.replace(/-/g, " ");
  const childDescription = childItem?.description || `Latest news from ${childTitle}`;

  return (
    <div className="container mx-auto p-4">
      <Breadcrumb parent={category} child={child} locale={locale} />
      <h1 className="text-2xl font-bold mb-4">{childTitle}</h1>
      <p className="text-gray-600 mb-6">{childDescription}</p>
      <NewsList
        initialNews={newsData.news}
        locale={locale}
        parent={child}
        initialPage={page}
        hasMore={!!newsData.next_page_url}
        total={newsData.total}
      />
    </div>
  );
}

export const dynamic = "force-dynamic";