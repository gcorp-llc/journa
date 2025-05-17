import type { NextConfig } from 'next';
import createNextIntlPlugin from 'next-intl/plugin';

const nextConfig: NextConfig = {
  images: {
    domains: [
      'core.journa.ir',
      // ...existing domains...
    ],
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'core.journa.ir',
        port: '',
        pathname: '/storage/**',
      },
      {
        protocol: 'https',
        hostname: 'core.journa.ir',
        port: '',
        pathname: '/storage/covers/Associated_Press/**',
      },
      {
        protocol: 'https',
        hostname: 'journa-core.test',
        port: '',
        pathname: '/storage/**',
      },
    ],
     
  },
  eslint: {
    ignoreDuringBuilds: false,
  },
  output: 'standalone',
  poweredByHeader: false,
  // اضافه کردن هدرهای عمومی برای API
  async headers() {
    return [
      {
        source: '/api/:path*',
        headers: [
          { key: 'Cache-Control', value: 'public, max-age=300' },
          { key: 'X-Content-Type-Options', value: 'nosniff' },
          { key: 'X-Frame-Options', value: 'DENY' },
        ],
      },
    ];
  },
  // اختیاری: اگر بخوای مسیرهای API مستقیم به بک‌اند برن
  /*
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: `${process.env.BASE_URL}/:path*`,
      },
    ];
  },
  */
};

const withNextIntl = createNextIntlPlugin();
export default withNextIntl(nextConfig);