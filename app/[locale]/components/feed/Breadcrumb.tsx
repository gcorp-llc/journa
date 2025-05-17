'use client';

import { useTranslations } from 'next-intl';
import { useParams } from 'next/navigation';
import { ChevronRight } from 'lucide-react';
import { Link } from '@/i18n/navigation';
import { Locale } from '@/types/common';

interface BreadcrumbItem {
  title: string;
  url: string;
}

interface BreadcrumbProps {
  parent?: string;
  child?: string;
  locale?: Locale;
}

export default function Breadcrumb({ parent, child, locale }: BreadcrumbProps) {
  const params = useParams();
  const tMenu = useTranslations('menu');
  const tNews = useTranslations('News');
  const effectiveParent = parent || (params.category as string) || 'home';
  const effectiveChild = child || (params.child as string);
  const effectiveLocale = locale || (params.locale as Locale) || 'fa';

  const items: BreadcrumbItem[] = [
    {
      title: tMenu('home.title') || 'Home',
      url: '/',
    },
  ];

  // اضافه کردن دسته‌بندی اصلی
  if (effectiveParent && effectiveParent !== 'home') {
    const parentTitle = tMenu(`${effectiveParent}.title`) || effectiveParent.replace(/-/g, ' ');
    items.push({
      title: parentTitle,
      url: effectiveChild ? `/${effectiveParent}` : '',
    });
  }

  // اضافه کردن زیرمجموعه
  if (effectiveChild) {
    const childItem = tMenu.raw(`${effectiveParent}.children`)?.find(
      (c: { url: string }) => {
        const normalizedUrl = c.url.split('/').pop() || c.url;
        return normalizedUrl === effectiveChild || c.url === effectiveChild;
      }
    );
    const childTitle = childItem?.title || effectiveChild.replace(/-/g, ' ');
    items.push({
      title: childTitle,
      url: '',
    });
  }

  return (
    <nav
      className="flex items-center gap-2 mb-6 p-4 bg-gradient-to-r from-amber-500 to-amber-600 rounded-xl shadow-sm font-vazir"
      aria-label="Breadcrumb"
    >
      {items.map((item, index) => (
        <div
          key={`${item.title}-${index}`}
          className="flex items-center gap-2 transition-all duration-300"
        >
          {item.url && index < items.length - 1 ? (
            <Link
              href={item.url}
              className="text-white hover:text-white hover:scale-105 transition-all duration-200 truncate max-w-[150px] font-medium"
              title={item.title}
            >
              {item.title}
            </Link>
          ) : (
            <span
              className="text-white truncate max-w-[200px] font-medium"
              title={item.title}
              aria-current={index === items.length - 1 ? 'page' : undefined}
            >
              {item.title}
            </span>
          )}
          {index < items.length - 1 && (
            <ChevronRight className="w-5 h-5 text-white flex-shrink-0" />
          )}
        </div>
      ))}
    </nav>
  );
}