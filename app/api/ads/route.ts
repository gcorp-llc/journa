import { NextRequest, NextResponse } from 'next/server';
import { AdItem } from '@/types/advertisement';

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const page = parseInt(searchParams.get('page') || '1', 10);
    const limit = parseInt(searchParams.get('limit') || '10', 10);
    const locale = searchParams.get('locale') || 'fa';

    // اعتبارسنجی ساده و سریع
    if (!['en', 'fa', 'ar'].includes(locale)) {
      return NextResponse.json({ error: 'Invalid locale' }, { status: 400 });
    }
    if (page < 1 || isNaN(page)) {
      return NextResponse.json({ error: 'Invalid page number' }, { status: 400 });
    }
    if (limit < 1 || isNaN(limit)) {
      return NextResponse.json({ error: 'Invalid limit' }, { status: 400 });
    }

    const baseUrl = process.env.BASE_URL || 'https://core.journa.ir/api/v1';
    // درخواست فقط فیلدهای ضروری
    const apiUrl = `${baseUrl}/ads?page=${page}&limit=${limit}&locale=${locale}&fields=id,title,subject,content,destination_url,cover,is_active`;

    const response = await fetch(apiUrl, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      next: { revalidate: 300 }, // کش برای 5 دقیقه
      signal: AbortSignal.timeout(3000), // تایم‌اوت 3 ثانیه
    });

    if (!response.ok) {
      return NextResponse.json(
        { error: `API error: ${response.status}` },
        { status: response.status }
      );
    }

    const contentType = response.headers.get('content-type');
    if (!contentType?.includes('application/json')) {
      return NextResponse.json(
        { error: 'Invalid response format' },
        { status: 500 }
      );
    }

    const data = await response.json();
    if (!data.success || !Array.isArray(data.data)) {
      return NextResponse.json(
        { error: 'Invalid response format' },
        { status: 500 }
      );
    }

    // مپینگ ساده و سبک
    const ads: Partial<AdItem>[] = data.data.map((ad: {
      id: number;
      title?: string;
      subject?: string;
      content?: string;
      destination_url?: string;
      cover?: string;
      is_active?: boolean;
    }) => ({
      id: ad.id,
      title: ad.title || '',
      subject: ad.subject || '',
      content: ad.content,
      destination_url: ad.destination_url || '',
      cover: ad.cover || '',
      is_active: ad.is_active ?? true,
    }));

    return NextResponse.json(
      { data: ads, success: true },
      {
        status: 200,
        headers: {
          'Cache-Control': 'public, max-age=300, s-maxage=300, stale-while-revalidate=60',
        },
      }
    );
  } catch (error) {
    console.error('Error in /api/ads:', error);
    return NextResponse.json(
      { error: error instanceof Error ? error.message : 'Internal server error' },
      { status: 500 }
    );
  }
}

export const dynamic = 'force-static';
export const revalidate = 300; // بازتولید هر 5 دقیقه