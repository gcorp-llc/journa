import type { Metadata } from "next";
import { Analytics } from "@vercel/analytics/next"
import localFont from "next/font/local";
import "../globals.css";
import { GoogleTagManager } from "@next/third-parties/google";
import { NextIntlClientProvider } from "next-intl";
import { getLangDir } from "rtl-detect";
import { notFound } from "next/navigation";
import { routing } from "@/i18n/routing";
import Header from "./components/layout/Header";
import AdSection from "./components/feed/AdSection";
import PageLoader from "./components/layout/PageLoader";
import ScrollToTopButton from "./components/ui/ScrollToTopButton";
import Footer from "./components/layout/Footer";

const shabnam = localFont({
  src: "./fonts/Shabnam-Bold.woff2",
  variable: "--font-geist-sans",
  weight: "100 900",
});
const vazir = localFont({
  src: "./fonts/Vazirmatn-Regular.woff2",
  variable: "--font-geist-mono",
  weight: "100 900",
});

export const metadata: Metadata = {
  title: "Journa News رسانه خبری ژورنا نیوز",
  description: "رسانه خبری ژورنا نیوز",
};

type Locale = "en" | "fa" | "ar";

export async function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}

export default async function RootLayout({
  children,
  params,
}: Readonly<{
  children: React.ReactNode;
  params: Promise<{ locale: Locale }>;
}>) {
  const { locale } = await params;

  if (!routing.locales.includes(locale)) {
    notFound();
  }

  let messages;
  try {
    messages = (await import(`../../locale/${locale}.json`)).default;
  } catch (error) {
    console.error(`Failed to load messages for locale: ${locale}`, error);
    notFound();
  }

  const direction = getLangDir(locale);

  return (
    <html data-theme="nord" lang={locale} dir={direction}>
      <head>
        <meta charSet="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link
          rel="shortcut icon"
          type="image/png"
          sizes="16x16"
          href="/favicon.png"
        />
        <link
          rel="shortcut icon"
          type="image/png"
          sizes="32x32"
          href="/favicon.png"
        />
        <link rel="apple-touch-icon" href="/favicon.png" />
      </head>
      <body className={`${vazir.variable} ${shabnam.variable} antialiased`}>
        <GoogleTagManager gtmId="GTM-5CZP6X6N" />
        <NextIntlClientProvider locale={locale} messages={messages}>
          <Header locale={locale} />
          <PageLoader />
          <div className="min-h-screen text-base-content md:mt-10 mt-20">
            {/* تبلیغات در موبایل در بالا */}
            <div className="md:hidden">
              <AdSection locale={locale} />
            </div>
            <div className="container mx-auto px-4 py-8">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div className="md:col-span-3">
                  <div className="flex-1">{children}
                      <Analytics />
                  </div>
                </div>
                {/* تبلیغات در دسکتاپ در ستون کناری */}
                <div className="hidden md:block md:col-span-1">
                  <AdSection locale={locale} />
                </div>
              </div>
            </div>
          </div>
          <ScrollToTopButton />
          <Footer />
        </NextIntlClientProvider>
      </body>
    </html>
  );
}
