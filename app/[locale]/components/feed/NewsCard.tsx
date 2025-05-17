"use client";

import Link from "next/link";
import { useTranslations } from "next-intl";
import Image from "next/image";
import { NewsItem } from "@/types/news";
import { Locale } from "@/types/common";
import { getAbsoluteUrl } from "@/lib/utils";

interface NewsCardProps {
  news: NewsItem;
  locale: Locale;
}

export default function NewsCard({ news, locale }: NewsCardProps) {
  const t = useTranslations("News");

  const formatDate = (dateString?: string): string => {
    if (!dateString) return t("noDate") || "بدون تاریخ";
    try {
      return new Date(dateString).toLocaleDateString(
        locale === "fa" ? "fa-IR" : locale === "ar" ? "ar-EG" : "en-US",
        {
          year: "numeric",
          month: "long",
          day: "numeric",
        }
      );
    } catch {
      return t("invalidDate") || "تاریخ نامعتبر";
    }
  };

  const cleanContent = (content: string) => {
    return content.replace(/<\/?[^>]+(>|$)/g, "").substring(0, 120) + "...";
  };

  // تصویر پیش‌فرض اگه cover وجود نداشته باشه
  // const imageSrc = news.cover?.replace(/`/g, "").trim() || "/placeholder.png";

  const imageSrc = news.cover
    ? getAbsoluteUrl(
        news.cover,
        process.env.NEXT_PUBLIC_BASE_IMAGE_URL ||
          "https://core.journa.ir/storage"
      )
    : "/placeholder.png";

  return (
    <Link
      href={`/${locale}/news/${news.slug}`}
      className="block"
      aria-label={news.title}
    >
      <div className="card bg-base-100 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 rounded-xl overflow-hidden">
        <div className="relative w-full h-48 bg-gray-100">
          <Image
            src={imageSrc}
            alt={news.title}
            fill
            sizes="(max-width: 768px) 100vw, 400px"
            className="object-cover transition-transform duration-500 hover:scale-105"
            loading="lazy"
            placeholder="blur"
            blurDataURL="/placeholder.png"
            onError={(e) => {
              console.error(
                `Failed to load image for news ${news.slug}: ${imageSrc}`
              );
              e.currentTarget.src = "/placeholder.png";
            }}
          />
        </div>
        <div className="card-body p-4">
          <div className="flex items-center justify-between mb-2">
            <h2 className="card-title text-lg font-semibold line-clamp-2">
              {news.title}
            </h2>
            {news.news_site_name && (
              <span className="text-xs text-gray-500">
                {news.news_site_name}
              </span>
            )}
          </div>
          <p className="text-xs text-gray-400 mb-2">
            {formatDate(news.published_at)}
          </p>
          <p className="text-gray-600 text-sm line-clamp-2">
            {cleanContent(news.content)}
          </p>
        </div>
      </div>
    </Link>
  );
}
