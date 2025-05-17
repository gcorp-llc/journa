import { create } from "zustand";
import { NewsItem, PaginatedNews } from "@/types/news";
import { CategoryData } from "@/types/category";
import { AdItem } from "@/types/advertisement";
import { Locale } from "@/types/common";

interface LocaleData {
  locale: Locale;
  newsData: CategoryData[];
  paginatedNews: PaginatedNews[];
  singleNews: Record<string, NewsItem>;
  ads: AdItem[];
}

interface NewsState {
  localeData: LocaleData[];
  setNewsData: (locale: Locale, data: CategoryData[]) => void;
  addPaginatedNews: (locale: Locale, data: PaginatedNews) => void;
  setSingleNews: (locale: Locale, news: NewsItem) => void;
  getSingleNews: (locale: Locale, slug: string) => NewsItem | undefined;
  setAds: (locale: Locale, ads: AdItem[]) => void;
  getNewsData: (locale: Locale) => CategoryData[];
  getPaginatedNews: (locale: Locale, category: string, page: number) => PaginatedNews | undefined;
  getAds: (locale: Locale) => AdItem[];
  clearLocaleData: (locale: Locale) => void;
  clearPaginatedNews: (locale: Locale, category: string) => void;
  clearAllData: () => void; // متد جدید برای پاک کردن تمام داده‌ها
}

export const useNewsStore = create<NewsState>((set, get) => ({
  localeData: [],
  setNewsData: (locale, data) =>
    set((state) => {
      const existingLocaleData = state.localeData.find((ld) => ld.locale === locale);
      if (existingLocaleData) {
        return {
          localeData: state.localeData.map((ld) =>
            ld.locale === locale ? { ...ld, newsData: data } : ld
          ),
        };
      }
      return {
        localeData: [
          ...state.localeData,
          { locale, newsData: data, paginatedNews: [], singleNews: {}, ads: [] },
        ],
      };
    }),
  addPaginatedNews: (locale, data) =>
    set((state) => {
      const existingLocaleData = state.localeData.find((ld) => ld.locale === locale);
      const uniqueNews = data.news.filter(
        (item) =>
          !existingLocaleData?.paginatedNews.some((pn) =>
            pn.news.some((existing) => existing.id === item.id)
          )
      );
      const updatedData = { ...data, news: uniqueNews };

      if (existingLocaleData) {
        const updatedPaginatedNews = [
          ...existingLocaleData.paginatedNews.filter(
            (item) => !(item.category === data.category && item.page === data.page)
          ),
          updatedData,
        ];
        return {
          localeData: state.localeData.map((ld) =>
            ld.locale === locale ? { ...ld, paginatedNews: updatedPaginatedNews } : ld
          ),
        };
      }
      return {
        localeData: [
          ...state.localeData,
          { locale, newsData: [], paginatedNews: [updatedData], singleNews: {}, ads: [] },
        ],
      };
    }),
  setSingleNews: (locale, news) =>
    set((state) => {
      const existingLocaleData = state.localeData.find((ld) => ld.locale === locale);
      if (existingLocaleData) {
        return {
          localeData: state.localeData.map((ld) =>
            ld.locale === locale
              ? { ...ld, singleNews: { ...ld.singleNews, [news.slug]: news } }
              : ld
          ),
        };
      }
      return {
        localeData: [
          ...state.localeData,
          { locale, newsData: [], paginatedNews: [], singleNews: { [news.slug]: news }, ads: [] },
        ],
      };
    }),
  getSingleNews: (locale, slug) =>
    get().localeData.find((ld) => ld.locale === locale)?.singleNews[slug],
  setAds: (locale, ads) =>
    set((state) => {
      const existingLocaleData = state.localeData.find((ld) => ld.locale === locale);
      if (existingLocaleData) {
        return {
          localeData: state.localeData.map((ld) =>
            ld.locale === locale ? { ...ld, ads } : ld
          ),
        };
      }
      return {
        localeData: [
          ...state.localeData,
          { locale, newsData: [], paginatedNews: [], singleNews: {}, ads },
        ],
      };
    }),
  getNewsData: (locale) =>
    get().localeData.find((ld) => ld.locale === locale)?.newsData || [],
  getPaginatedNews: (locale, category, page) =>
    get().localeData
      .find((ld) => ld.locale === locale)
      ?.paginatedNews.find((item) => item.category === category && item.page === page),
  getAds: (locale) =>
    get().localeData.find((ld) => ld.locale === locale)?.ads || [],
  clearLocaleData: (locale) =>
    set((state) => ({
      localeData: state.localeData.filter((ld) => ld.locale !== locale),
    })),
  clearPaginatedNews: (locale, category) =>
    set((state) => {
      const existingLocaleData = state.localeData.find((ld) => ld.locale === locale);
      if (existingLocaleData) {
        const updatedPaginatedNews = existingLocaleData.paginatedNews.filter(
          (item) => item.category !== category
        );
        return {
          localeData: state.localeData.map((ld) =>
            ld.locale === locale ? { ...ld, paginatedNews: updatedPaginatedNews } : ld
          ),
        };
      }
      return state;
    }),
  clearAllData: () =>
    set(() => ({
      localeData: [],
    })),
}));