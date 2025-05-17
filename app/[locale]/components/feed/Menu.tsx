'use client';

import { useTranslations } from 'next-intl';
import { usePathname } from 'next/navigation';
import {
  Globe, Briefcase, DollarSign, CircuitBoard, Microscope, Wallet,
  Building, Rss, Heart, Paintbrush, Lightbulb, HeartPulse, Trophy, MapPinHouse
} from 'lucide-react';
import { Link } from '@/i18n/navigation';

const iconMap = {
  Globe,
  Briefcase,
  DollarSign,
  CircuitBoard,
  Microscope,
  Wallet,
  Building,
  Rss,
  MapPinHouse,
  Heart,
  Paintbrush,
  Lightbulb,
  HeartPulse,
  Trophy
};

interface MenuChild {
  title: string;
  url: string;
  description?: string;
}

interface MenuItem {
  title: string;
  url: string;
  icon?: string | null;
  description?: string;
  children?: MenuChild[];
}

export default function Menu({ onLinkClick }: { onLinkClick: () => void }) {
  const t = useTranslations('menu');
  const pathname = usePathname();

  // کلیدهای منو (حذف search)
  const menuKeys = [
    'home', 'world', 'business', 'economy', 'tech', 'science',
    'personal-finance', 'companies', 'work-careers', 'real-estate', 'lifestyle',
    'arts', 'health', 'sports', 'opinion'
  ];

  // گرفتن دسته‌بندی فعلی (بدون لوکال)
  const currentCategory = pathname
    .replace(/^\/(fa|en|ar)\//, '') // حذف لوکال
    .split('?')[0] // حذف پارامترهای کوئری
    .replace(/^\//, ''); // حذف اسلش ابتدایی

  const isActive = (url: string) => {
    const normalizedUrl = url.replace(/^\//, '');
    return currentCategory === normalizedUrl || currentCategory.startsWith(normalizedUrl + '/');
  };

  return (
    <div className="drawer-side">
      <label
        htmlFor="my-drawer"
        aria-label="close sidebar"
        className="drawer-overlay"
      ></label>
      <ul className="menu bg-base-100 text-base-content min-h-full w-80 p-4 shadow-lg">
        {menuKeys.map((key) => {
          // گرفتن داده‌های منو
          let menuItem: MenuItem;
          try {
            menuItem = {
              title: t(`${key}.title`) || key,
              url: (t.raw(`${key}.url`) as string) || '/',
              icon: t.raw(`${key}.icon`) as string | null,
              description: t(`${key}.description`) || '',
              children: (t.raw(`${key}.children`) as MenuChild[]) || []
            };
          } catch (e) {
            console.error(`Failed to load menu item for ${key}:`, e);
            return null;
          }

          const { title, url, icon, children } = menuItem;

          // گرفتن آیکون با مدیریت خطا
          const Icon = icon && iconMap[icon as keyof typeof iconMap] ? iconMap[icon as keyof typeof iconMap] : Globe;

          // چک کردن فعال بودن منو (برای باز شدن یا رنگ)
          const isMenuActive = isActive(url) ||
            (children && children.some(child => isActive(child.url)));

          // چک کردن آیا پرنت دقیقاً فعال است (برای رنگ)
          const isParentSelected = isActive(url) &&
            (!children || !children.some(child => isActive(child.url)));

          // لاگ برای دیباگ (فقط در توسعه)
          if (process.env.NODE_ENV !== 'production') {
            console.log(`Menu: Generating link for ${key}, URL: ${url}, Active: ${isMenuActive}`);
          }

          return (
            <li key={key} className="mb-2">
              {Array.isArray(children) && children.length > 0 ? (
                <details open={isMenuActive} className="group">
                  <summary className={`flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-colors duration-200 ${
                    isParentSelected
                      ? 'bg-amber-500 text-primary-content'
                      : 'hover:bg-amber-500 hover:text-primary-content'
                  }`}>
                    <Icon className="w-5 h-5 group-open:rotate-90 transition-transform duration-200" />
                    <span className="font-medium">{title}</span>
                  </summary>
                  <ul className="pl-6 pt-2 pb-1 space-y-1 transition-all duration-300 ease-in-out max-h-96 overflow-y-auto">
                    {children.map((child, index) => {
                      const childUrl = child.url.startsWith('/') ? child.url : `/${child.url}`;
                      const isChildActive = isActive(child.url);

                      if (process.env.NODE_ENV !== 'production') {
                        console.log(`Child: ${child.title}, URL: ${childUrl}, Active: ${isChildActive}`);
                      }

                      return (
                        <li key={`${key}-child-${index}`}>
                          <Link
                            onClick={onLinkClick}
                            href={childUrl}
                            className={`flex items-center gap-3 px-4 py-2 rounded-lg transition-colors duration-200 ${
                              isChildActive
                                ? 'bg-amber-600 text-white'
                                : 'hover:bg-amber-600 hover:text-white'
                            }`}
                          >
                            <span>{child.title}</span>
                          </Link>
                        </li>
                      );
                    })}
                  </ul>
                </details>
              ) : (
                <Link
                  href={url}
                  onClick={onLinkClick}
                  className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 ${
                    isMenuActive ? 'bg-amber-500 text-primary-content' : 'hover:bg-amber-500 hover:text-primary-content'
                  }`}
                >
                  <Icon className="w-5 h-5" />
                  <span className="font-medium">{title}</span>
                </Link>
              )}
            </li>
          );
        })}
      </ul>
    </div>
  );
}