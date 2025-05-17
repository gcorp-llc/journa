'use client';

import { ChevronUp } from 'lucide-react';
import { useState, useEffect } from 'react';


export default function ScrollToTopButton() {
  // مدیریت حالت نمایش/مخفی شدن دکمه
  const [isVisible, setIsVisible] = useState(false);

  // اضافه کردن listener برای اسکرول
  useEffect(() => {
    const toggleVisibility = () => {
      if (window.scrollY > 300) {
        setIsVisible(true);
      } else {
        setIsVisible(false);
      }
    };

    window.addEventListener('scroll', toggleVisibility);

    return () => window.removeEventListener('scroll', toggleVisibility);
  }, []);

  // تابع اسکرول به بالای صفحه
  const scrollToTop = () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  };

  return (
    <button
      onClick={scrollToTop}
      className={`fixed bottom-6 right-6 z-50 flex items-center justify-center w-12 h-12 bg-amber-500 text-white rounded-full shadow-lg transition-all duration-300 ease-in-out ${
        isVisible ? 'opacity-100 scale-100' : 'opacity-0 scale-50 pointer-events-none'
      } hover:bg-amber-600 hover:scale-110 hover:rotate-12`}
      aria-label="رفتن به بالای صفحه"
    >
     <ChevronUp />
    </button>
  );
}