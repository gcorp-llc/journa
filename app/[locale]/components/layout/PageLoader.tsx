'use client';

import { usePathname, useRouter } from 'next/navigation';
import { useEffect, useState, useRef } from 'react';
import Image from 'next/image';

export default function PageLoader() {
  const pathname = usePathname();
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const prevPathnameRef = useRef<string | null>(null);
  const timeoutRef = useRef<NodeJS.Timeout | null>(null);

  useEffect(() => {
    // پاک‌سازی تایم‌اوت قبلی
    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current);
    }

    // اگر pathname تغییر کرده، لودر را فعال کن
    if (prevPathnameRef.current !== pathname && prevPathnameRef.current !== null) {
      setLoading(true);
      // حداقل زمان لودر برای انیمیشن روان
      timeoutRef.current = setTimeout(() => {
        setLoading(false);
      }, 500);
    }

    // به‌روزرسانی pathname قبلی
    prevPathnameRef.current = pathname;

    // پاک‌سازی هنگام unmount
    return () => {
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }
    };
  }, [pathname]);

  // پیش‌بارگذاری مسیرهای بعدی (اختیاری)
  useEffect(() => {
    // می‌توانید لینک‌های موجود در صفحه را پیش‌بارگذاری کنید
    // مثال: router.prefetch('/some-path');
  }, [router]);

  return (
    <div
      className={`fixed inset-0 z-50 flex items-center justify-center bg-slate-100 bg-opacity-30 transition-opacity duration-300 ${
        loading ? 'opacity-70 pointer-events-auto' : 'opacity-0 pointer-events-none'
      }`}
      aria-hidden={!loading}
    >
      <div className="relative w-28 h-28">
        <Image
          src="/favicon.png" // مسیر لوگوی سایت
          alt="در حال بارگذاری"
          fill
          className="object-contain animate-blur-pulse"
          priority
        />
      </div>
      <style jsx>{`
        @keyframes blur-pulse {
          0% {
            filter: blur(6px) brightness(1);
          }
          50% {
            filter: blur(2px) brightness(1.4);
          }
          100% {
            filter: blur(6px) brightness(1);
          }
        }
        .animate-blur-pulse {
          animation: blur-pulse 1.2s infinite ease-in-out;
        }
      `}</style>
    </div>
  );
}