'use client';

import { useTranslations } from 'next-intl';
import { useRouter, usePathname } from 'next/navigation';
import { Languages } from 'lucide-react';
import { useCallback } from 'react';

export default function LanguageSwitcher() {
  const router = useRouter();
  const pathname = usePathname();
  const t = useTranslations();
  const currentLocale = pathname.split('/')[1]; // استخراج زبان فعلی از URL (مثل fa، en، ar)

  const changeLanguage = useCallback(
    (locale: string) => {
      // ذخیره زبان در کوکی
      document.cookie = `NEXT_LOCALE=${locale}; path=/; max-age=31536000`; // ذخیره برای یک سال

      // جایگزینی زبان در مسیر فعلی
      const newPath = pathname.replace(/^\/[a-z]{2}/, `/${locale}`);
      router.push(newPath);
    },
    [pathname, router]
  );

  return (
    <div className="dropdown dropdown-end">
      <div tabIndex={0} role="button" className="btn btn-circle text-white btn-ghost boeder-none m-1 hover:bg-amber-600">
        <Languages />
      </div>
      <ul
        tabIndex={0}
        className="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow-sm"
      >
        <li>
          <button
            onClick={() => changeLanguage('fa')}
            disabled={currentLocale === 'fa'}
            className={currentLocale === 'fa' ? 'text-gray-400 cursor-not-allowed' : ''}
          >
            {t('language.fa')}
          </button>
        </li>
        <li>
          <button
            onClick={() => changeLanguage('en')}
            disabled={currentLocale === 'en'}
            className={currentLocale === 'en' ? 'text-gray-400 cursor-not-allowed' : ''}
          >
            {t('language.en')}
          </button>
        </li>
        <li>
          <button
            onClick={() => changeLanguage('ar')}
            disabled={currentLocale === 'ar'}
            className={currentLocale === 'ar' ? 'text-gray-400 cursor-not-allowed' : ''}
          >
            {t('language.ar')}
          </button>
        </li>
      </ul>
    </div>
  );
}