'use client';

import { useTranslations } from 'next-intl';
import { useRouter } from '@/i18n/navigation';
import { ArrowLeft, Search } from 'lucide-react';
import { motion } from 'framer-motion';
import { Locale } from '@/types/common';
import Image from 'next/image';

type NotFoundPageProps = {
  params: {
    locale: Locale;
  };
};

export default function NotFoundPage({ params }: NotFoundPageProps) {
  const { locale } = params;
  const t = useTranslations('NotFound');
  const router = useRouter();

  const containerVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 0.6,
        when: 'beforeChildren',
        staggerChildren: 0.2,
      },
    },
  };

  const itemVariants = {
    hidden: { opacity: 0, y: 10 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.4 } },
  };

  return (
    <motion.div
      className="min-h-screen bg-gradient-to-b from-base-200 to-base-100 flex items-center justify-center px-4 py-12"
      variants={containerVariants}
      initial="hidden"
      animate="visible"
    >
      <div className="max-w-4xl w-full grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        {/* Illustration */}
        <motion.div variants={itemVariants} className="relative">
          <Image
            src="/404-illustration.png"
            alt={t('imageAlt') || '404 Not Found'}
            width={400}
            height={400}
            className="object-contain"
            priority
            aria-hidden="true"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-transparent to-base-100/20 rounded-full animate-pulse" />
        </motion.div>

        {/* Content */}
        <motion.div variants={itemVariants} className="text-center md:text-left space-y-6">
          <h1 className="text-5xl font-bold text-primary tracking-tight">
            {t('title') || '404 - صفحه یافت نشد'}
          </h1>
          <p className="text-lg text-base-content/80">
            {t('description') ||
              'متأسفیم، صفحه‌ای که به دنبال آن هستید وجود ندارد یا منتقل شده است.'}
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
            <motion.button
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.95 }}
              onClick={() => router.push('/')}
              className="btn btn-primary flex items-center gap-2"
              aria-label={t('goHome') || 'بازگشت به خانه'}
            >
              <ArrowLeft className="w-5 h-5" />
              {t('goHome') || 'بازگشت به خانه'}
            </motion.button>
            <motion.button
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.95 }}
              onClick={() => router.push('/search')}
              className="btn btn-outline flex items-center gap-2"
              aria-label={t('search') || 'جستجو در سایت'}
            >
              <Search className="w-5 h-5" />
              {t('search') || 'جستجو در سایت'}
            </motion.button>
          </div>
        </motion.div>
      </div>
    </motion.div>
  );
}