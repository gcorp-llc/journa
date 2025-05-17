import { getTranslations } from "next-intl/server";
import { notFound } from "next/navigation";
import { VALID_CATEGORIES, ValidCategory } from "@/config/categories";
import NewsList from "../components/feed/NewsList";
import Breadcrumb from "../components/feed/Breadcrumb";
import AlertError from "../components/ui/AlertError";
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
  category?: string;
}

interface MenuChild {
  title: string;
  url: string;
}

function isValidCategory(category: string): category is ValidCategory {
  return VALID_CATEGORIES.includes(category as ValidCategory);
}

async function fetchCategoryNews(
  locale: Locale,
  category: string,
  page: number
): Promise<PaginatedNews> {
  const perPage = 33;
  const baseUrl = process.env.NEXT_PUBLIC_BASE_URL || "http://localhost:3000";
  const apiUrl = `${baseUrl}/api/news?locale=${locale}&parent=${category}&page=${page}&perPage=${perPage}`;

  try {
    if (process.env.NODE_ENV !== "production") {
      console.log(`Fetching category news from: ${apiUrl}`);
    }
    
    const response = await fetch(apiUrl, {
      method: 'GET',
      headers: {
        "Content-Type": "application/json",
        "Accept-Language": locale,
      },
      next: { revalidate: 300 } // Using Next.js fetch revalidate option
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
      console.log(`Formatted category news: count=${formattedNews.length}`);
    }

    return {
      category,
      page,
      news: formattedNews,
      next_page_url: next_page_url && typeof next_page_url === "string" ? next_page_url : null,
      total,
    };
  } catch (error) {
    console.error("Error fetching category news:", error);
    throw error;
  }
}

export async function generateMetadata({
  params: paramsPromise,
}: {
  params: Promise<{ locale: Locale; category: string }>;
}) {
  const { locale, category } = await paramsPromise;
  const baseUrl = process.env.NEXT_PUBLIC_BASE_URL || "https://journa.ir";
  const tMenu = await getTranslations("menu");

  if (!isValidCategory(category)) {
    if (process.env.NODE_ENV !== "production") {
      console.log(`Invalid category: ${category}`);
    }
    return {
      title: `${tMenu("notFound")} | Journa`,
      description: tMenu("missingCategory") || "Category not found.",
    };
  }

  const mainCategory = category.split("/")[0];
  const subCategory = category.split("/")[1];
  const translationKey = mainCategory.replace(/-/g, "");
  const children = (tMenu.raw(`${translationKey}.children`) as MenuChild[]) || [];
  const categoryTitle = subCategory
    ? children.find((child) => child.url === category)?.title || category.replace(/-/g, " ")
    : tMenu(`${translationKey}.title`) || category.replace(/-/g, " ");

  const pageTitle = `${categoryTitle} | Journa`;
  const pageDescription = tMenu(`${translationKey}.description`) || `Latest news from ${categoryTitle}`;

  return {
    title: pageTitle,
    description: pageDescription,
    keywords: `${categoryTitle}, news, journa`,
    robots: "index, follow",
    alternates: {
      canonical: `${baseUrl}/${locale}/${category}`,
      languages: {
        fa: `${baseUrl}/fa/${category}`,
        en: `${baseUrl}/en/${category}`,
        ar: `${baseUrl}/ar/${category}`,
        "x-default": `${baseUrl}/en/${category}`,
      },
    },
    openGraph: {
      title: pageTitle,
      description: pageDescription,
      type: "website",
      url: `${baseUrl}/${locale}/${category}`,
      images: [{ url: `${baseUrl}/favicon.jpg`, alt: categoryTitle }],
      locale: locale === "fa" ? "fa_IR" : locale === "ar" ? "ar_AR" : "en_US",
      siteName: "Journa",
    },
    twitter: {
      card: "summary_large_image",
      title: pageTitle,
      description: pageDescription,
      images: [{ url: `${baseUrl}/favicon.jpg`, alt: categoryTitle }],
      site: "@Journa",
    },
    other: {
      "script:ld+json": JSON.stringify({
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        name: pageTitle,
        description: pageDescription,
        url: `${baseUrl}/${locale}/${category}`,
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
              name: categoryTitle,
              item: `${baseUrl}/${locale}/${category}`,
            },
          ],
        },
      }),
    },
  };
}

type Props = {
  params: Promise<{ locale: Locale; category: string }>;
  searchParams: Promise<{ page?: string }>;
};

export default async function CategoryPage({
  params: paramsPromise,
  searchParams: searchParamsPromise,
}: Props) {
  const { locale, category } = await paramsPromise;
  const { page: pageStr = "1" } = await searchParamsPromise;
  const page = Math.max(1, parseInt(pageStr) || 1);

  const tNews = await getTranslations("News");
  const tMenu = await getTranslations("menu");

  if (!["en", "fa", "ar"].includes(locale) || !isValidCategory(category)) {
    if (process.env.NODE_ENV !== "production") {
      console.log(`Invalid access: locale=${locale}, category=${category}`);
    }
    notFound();
  }

  let newsData: PaginatedNews;
  try {
    const storedNews = useNewsStore.getState().getPaginatedNews(locale, category, page);
    if (storedNews) {
      if (process.env.NODE_ENV !== "production") {
        console.log(`Using cached news: locale=${locale}, category=${category}, page=${page}`);
      }
      newsData = storedNews;
    } else {
      newsData = await fetchCategoryNews(locale, category, page);
      useNewsStore.getState().addPaginatedNews(locale, newsData);
      if (process.env.NODE_ENV !== "production") {
        console.log(
          `Stored news: locale=${locale}, category=${category}, page=${page}, count=${
            newsData.news.length
          }`
        );
      }
    }
  } catch (error) {
    console.error("Category page error:", error);
    return (
      <div className="container mx-auto p-4">
        <Breadcrumb parent={category} locale={locale} />
        <AlertError message={tNews("error") || "خطأ في تحميل الأخبار."} />
      </div>
    );
  }

  if (newsData.news.length === 0) {
    return (
      <div className="container mx-auto p-4">
        <Breadcrumb parent={category} locale={locale} />
        <p className="text-lg text-gray-500">
          {tNews("noContent") || "لم يتم العثور على أخبار."}
        </p>
      </div>
    );
  }

  const mainCategory = category.split("/")[0];
  const subCategory = category.split("/")[1];
  const translationKey = mainCategory.replace(/-/g, "");
  const children = (tMenu.raw(`${translationKey}.children`) as MenuChild[]) || [];
  const categoryTitle = subCategory
    ? children.find((child) => child.url === category)?.title || category.replace(/-/g, " ")
    : tMenu(`${translationKey}.title`) || category.replace(/-/g, " ");
  const categoryDescription = tMenu(`${translationKey}.description`) || `Latest news from ${categoryTitle}`;

  return (
    <div className="container mx-auto p-4">
      <Breadcrumb parent={category} locale={locale} />
      <h1 className="text-2xl font-bold mb-4">{categoryTitle}</h1>
      <p className="text-lg text-gray-600 mb-4">{categoryDescription}</p>
      <NewsList
        initialNews={newsData.news}
        locale={locale}
        parent={category}
        initialPage={page}
        hasMore={!!newsData.next_page_url}
        total={newsData.total}
      />
    </div>
  );
}

export const dynamic = "force-dynamic";