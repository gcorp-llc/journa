import { NextRequest, NextResponse } from 'next/server';
import { NewsItem } from '@/types/news';
import { RouteParams, NewsParams } from '@/types/route';

interface NewsApiResponse {
  data?: NewsItem;
  error?: string;
  message?: string;
}

export async function GET(
  request: NextRequest,
  { params }: RouteParams<NewsParams>
): Promise<NextResponse<NewsApiResponse>> {
  try {
    const { slug } = await params;
    const { searchParams } = new URL(request.url);
    const locale = searchParams.get('locale') || 'fa';

    const allowedLocales = ['en', 'fa', 'ar'];
    if (!allowedLocales.includes(locale)) {
      return NextResponse.json(
        { error: 'Invalid locale', message: `Locale must be one of: ${allowedLocales.join(', ')}` },
        { status: 400 }
      );
    }

    // اضافه کردن فیلدهای views و source_url به درخواست
    const apiUrl = `${process.env.BASE_URL}/news/${slug}?locale=${locale}&fields=id,title,content,slug,published_at,cover,news_site_name,logo_url,views,source_url`;

    const response = await fetch(apiUrl, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept-Language': locale,
      },
      cache: 'no-store',
    });

    if (!response.ok) {
      const errorData = await response.json();
      return NextResponse.json(
        { error: errorData.error || 'Failed to fetch news', message: errorData.message },
        { status: response.status }
      );
    }

    const data = await response.json();
    return NextResponse.json(data, {
      headers: {
        'Cache-Control': 'public, max-age=300',
      },
    });
  } catch (error) {
    console.error('API error:', error);
    return NextResponse.json(
      { error: 'Failed to fetch news', message: 'An error occurred' },
      { status: 500 }
    );
  }
}