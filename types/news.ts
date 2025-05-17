export interface NewsItem {
  id: number;
  title: string;
  content: string;
  slug: string; // slug به‌عنوان ویژگی اجباری تعریف شده است
  published_at: string;
  cover?: string;
  news_site_name?: string;
  logo_url?: string;
  views?: number;
  source_url?: string;
}

export interface NewsContent {
  id: number;
  title: string;
  content: string;
  cover: string | null;
  published_at?: string;
  news_site_name?: string;
  slug: string;
  category_slug?: string;
  views?: number; // تعداد بازدید
  source_url?: string; // لینک منبع
}

export interface PaginatedNews {
  category: string;
  page: number;
  news: NewsItem[];
  next_page_url: string | null;
  total: number;
}

export enum NewsStatus {
  Draft = 'draft',
  Published = 'published',
  Archived = 'archived',
}

export interface Media {
  id: number;
  news_id: number;
  file_path: string;
  file_type: MediaFileType;
  caption?: string;
  created_at?: string;
  updated_at?: string;
}

export enum MediaFileType {
  Image = 'image',
  Video = 'video',
  Pdf = 'pdf',
  Other = 'other',
}