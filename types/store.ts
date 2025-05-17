// types/store.ts
import { CategoryData } from './category';
import {  PaginatedNews } from './news';


export interface LocaleData {
  locale: string;
  newsData: CategoryData[];
  paginatedNews: PaginatedNews[];
}

export interface NewsState {
  localeData: LocaleData[];
  setNewsData: (locale: string, data: CategoryData[]) => void;
  addPaginatedNews: (locale: string, data: PaginatedNews) => void;
  getNewsData: (locale: string) => CategoryData[];
  getPaginatedNews: (locale: string, category: string, page: number) => PaginatedNews | undefined;
  clearLocaleData: (locale: string) => void;
}