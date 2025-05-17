export interface NewsSite {
  id: number;
  name: string;
  description?: string;
  logo_url?: string;
  site_url: string;
  created_at?: string;
  updated_at?: string;
}

export interface NewsSiteCategory {
  id: number;
  news_site_id: number;
  category_id: number;
  category_url: string;
  created_at?: string;
  updated_at?: string;
}