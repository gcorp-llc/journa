import { NextRequest, NextResponse } from "next/server";

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const parent = searchParams.get("parent") || "";
    const child = searchParams.get("child") || "";
    const query = searchParams.get("query") || "";
    const page = searchParams.get("page") || "1";
    const perPage = searchParams.get("perPage") || "33";
    const locale = searchParams.get("locale") || "fa";

    if (isNaN(Number(page)) || isNaN(Number(perPage)) || Number(page) < 1 || Number(perPage) < 1) {
      return NextResponse.json(
        { error: "Invalid parameters", message: "Page and perPage must be positive integers" },
        { status: 400 }
      );
    }

    const allowedLocales = ["en", "fa", "ar"];
    if (!allowedLocales.includes(locale)) {
      return NextResponse.json(
        { error: "Invalid locale", message: `Locale must be one of: ${allowedLocales.join(", ")}` },
        { status: 400 }
      );
    }

    const apiUrl = new URL(`${process.env.BASE_URL}/news`);
    if (parent) apiUrl.searchParams.set("parent", parent);
    if (child) apiUrl.searchParams.set("child", child);
    if (query) apiUrl.searchParams.set("query", query);
    apiUrl.searchParams.set("page", page);
    apiUrl.searchParams.set("perPage", perPage);
    apiUrl.searchParams.set("locale", locale);

    if (process.env.NODE_ENV !== "production") {
      console.log(`Fetching from backend: ${apiUrl.toString()}`);
    }

    const response = await fetch(apiUrl, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Accept-Language": locale,
      },
      next: { revalidate: 300 }, // کش برای 5 دقیقه
    });

    if (!response.ok) {
      const errorData = await response.json();
      return NextResponse.json(
        { error: errorData.error || "Failed to fetch news", message: errorData.message },
        { status: response.status }
      );
    }

    const data = await response.json();
    return NextResponse.json(data, {
      headers: { "Cache-Control": "public, max-age=300" },
    });
  } catch (error) {
    console.error("API error:", error);
    return NextResponse.json(
      { error: "Failed to fetch news", message: "An error occurred" },
      { status: 500 }
    );
  }
}