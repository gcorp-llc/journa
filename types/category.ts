import { NewsItem } from './news';

export interface Category {
  id: number;
  slug: string;
  title: string;
  icon?: string;
  parent_id?: number;
}

export interface CategoryData {
  category: Category;
  news: NewsItem[];
}

export interface CategoryNews {
  id: number;
  news_id: number;
  category_id: number;
  created_at?: string;
  updated_at?: string;
}