import { NextRequest, NextResponse } from 'next/server';

type NewsItem = {
  id: number;
  title: string;
  content: string;
  slug: string;
  published_at: string;
  image?: string;
  category?: string;
};

type BackendApiResponse = {
  data: NewsItem[];
  next_page_url: string | null;
  total: number;
  message?: string;
};

type ErrorResponse = {
  error: string;
  message?: string;
  code?: string;
};

const BACKEND_URL = process.env.BASE_URL || 'https://core.journa.ir';

export async function GET(request: NextRequest): Promise<NextResponse<BackendApiResponse | ErrorResponse>> {
  const searchParams = request.nextUrl.searchParams;
  const query = searchParams.get('query')?.trim();
  const page = searchParams.get('page') || '1';
  const locale = searchParams.get('locale') || 'fa';

  // اعتبارسنجی ورودی‌ها
  if (!query) {
    return NextResponse.json(
      { error: 'Bad Request', message: 'Search query parameter is required.' },
      { status: 400 }
    );
  }
  if (!['en', 'fa', 'ar'].includes(locale)) {
    return NextResponse.json(
      { error: 'Invalid locale', message: 'Locale must be en, fa, or ar.' },
      { status: 400 }
    );
  }
  if (isNaN(Number(page)) || Number(page) < 1) {
    return NextResponse.json(
      { error: 'Invalid page', message: 'Page must be a positive number.' },
      { status: 400 }
    );
  }

  try {
    const backendApiUrl = new URL(`${BACKEND_URL}/search`);
    backendApiUrl.searchParams.append('query', query);
    backendApiUrl.searchParams.append('page', page);
    backendApiUrl.searchParams.append('locale', locale);
    backendApiUrl.searchParams.append('perPage', '33');

    console.log('Fetching from:', backendApiUrl.toString());

    const response = await fetch(backendApiUrl.toString(), {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      signal: AbortSignal.timeout(10000), // معادل timeout در axios
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      const errorMessage =
        response.status === 503 ? 'Connection to backend server failed' :
        response.status === 504 ? 'Request timeout' :
        'Failed to fetch search results';
      const errorDetails =
        response.status === 503 ? 'Could not connect to the backend server. Please ensure the Laravel server is running.' :
        response.status === 504 ? 'The request took too long to complete. Please try again.' :
        errorData.message || 'Unknown error';

      console.error('Search API error:', {
        message: errorMessage,
        details: errorDetails,
        code: `HTTP_${response.status}`,
      });

      return NextResponse.json(
        {
          error: errorMessage,
          message: errorDetails,
          code: `HTTP_${response.status}`,
        },
        { status: response.status }
      );
    }

    const data: BackendApiResponse = await response.json();

    if (!Array.isArray(data.data)) {
      throw new Error('Invalid response format from backend API');
    }

    // فیلتر کردن آیتم‌های تکراری
    const uniqueData = Array.from(
      new Map(data.data.map((item) => [item.id, item])).values()
    );
    console.log('Unique items from API:', { count: uniqueData.length });

    return NextResponse.json({
      ...data,
      data: uniqueData,
    });
  } catch (error: unknown) {
    const errorMessage = error instanceof Error && error.name === 'TimeoutError'
      ? 'Request timeout'
      : 'An unexpected error occurred';
    const errorDetails = error instanceof Error
      ? error.message
      : String(error);
    const errorCode = error instanceof Error && error.name === 'TimeoutError'
      ? 'TIMEOUT'
      : 'UNKNOWN_ERROR';

    console.error('Search API error:', {
      message: errorMessage,
      details: errorDetails,
      code: errorCode,
    });

    return NextResponse.json(
      {
        error: errorMessage,
        message: errorDetails,
        code: errorCode,
      },
      { status: 500 }
    );
  }
}