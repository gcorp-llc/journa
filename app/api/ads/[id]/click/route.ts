import { NextRequest, NextResponse } from 'next/server';

type RouteParams = {
  params: Promise<{ id: string }>;
};

export async function POST(request: NextRequest, { params }: RouteParams) {
  try {
    const resolvedParams = await params; // باز کردن Promise
    const { id } = resolvedParams;

    if (!id || isNaN(Number(id))) {
      return NextResponse.json(
        { error: 'Invalid ad ID', success: false },
        { status: 400 }
      );
    }

    const baseUrl = process.env.BASE_URL || 'https://core.journa.ir/api/v1';
    const apiUrl = `${baseUrl}/ads/${id}/click`;

    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Cache-Control': 'no-store',
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status} ${response.statusText}`);
    }

    const data = await response.json();
    return NextResponse.json(
      { success: data.success, message: 'Click tracked' },
      { status: 200 }
    );
  } catch (error) {
    console.error('Error tracking ad click:', error);
    return NextResponse.json(
      {
        error: 'Failed to track click',
        message: error instanceof Error ? error.message : 'Unknown error',
        success: false,
      },
      { status: 500 }
    );
  }
}
