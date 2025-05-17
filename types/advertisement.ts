export interface AdItem {
  id: number;
  title: string;
  subject: string;
  content?: string;
  destination_url: string;
  cover: string;
  start_date?: string;
  end_date?: string;
  max_impressions?: number;
  max_clicks?: number;
  current_impressions: number;
  current_clicks: number;
  is_active: boolean;
}