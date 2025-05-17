import { NextRequest, NextResponse } from 'next/server';
import { RouteParams, NewsParams } from '@/types/route';

interface IncrementViewsResponse {
  success?: boolean;
  views?: number;
  error?: string;
  message?: string;
}

export async function POST(
  request: NextRequest,
  { params }: RouteParams<NewsParams>
): Promise<NextResponse<IncrementViewsResponse>> {
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

    const apiUrl = `${process.env.BASE_URL}/news/${slug}/increment-views?locale=${locale}`;

    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept-Language': locale,
      },
      cache: 'no-store',
    });

    if (!response.ok) {
      const errorData = await response.json();
      return NextResponse.json(
        { error: errorData.error || 'Failed to increment views', message: errorData.message },
        { status: response.status }
      );
    }

    const data = await response.json();
    return NextResponse.json({
      success: true,
      views: data.views || 0,
    });
  } catch (error) {
    console.error('API error:', error);
    return NextResponse.json(
      { error: 'Failed to increment views', message: 'An error occurred' },
      { status: 500 }
    );
  }
}