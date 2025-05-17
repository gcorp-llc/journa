'use client';

import { useState, useEffect, useRef } from 'react';
import { useTranslations } from 'next-intl';
import { Search, Loader2, X } from 'lucide-react';
import { useRouter } from '@/i18n/navigation';
import { Locale } from '@/types/common';
import debounce from 'lodash/debounce';

type SearchBoxProps = {
  locale: Locale;
  className?: string;
  onClose?: () => void;
  autoFocus?: boolean;
};

export default function SearchBox({
  locale,
  className = '',
  onClose,
  autoFocus = false,
}: SearchBoxProps) {
  const t = useTranslations('Search');
  const router = useRouter();
  const [query, setQuery] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (autoFocus && inputRef.current) {
      inputRef.current.focus();
    }
  }, [autoFocus]);

  const handleSearch = debounce(async () => {
    const trimmedQuery = query.trim().replace(/[<>]/g, ''); // فیلتر کاراکترهای خطرناک
    if (!trimmedQuery || trimmedQuery.length < 3 || isLoading) {
      console.log('Search skipped:', { trimmedQuery, isLoading });
      return;
    }

    try {
      setIsLoading(true);
      await router.push(`/search?query=${encodeURIComponent(trimmedQuery)}`);
      console.log('Navigated to search:', trimmedQuery);
      if (inputRef.current) {
        inputRef.current.blur();
      }
      if (onClose) {
        onClose();
      }
    } catch (err) {
      console.error('Error navigating to search:', err);
    } finally {
      setIsLoading(false);
    }
  }, 300);

  const handleClear = () => {
    setQuery('');
    if (inputRef.current) {
      inputRef.current.focus();
    }
    console.log('Search query cleared');
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleSearch();
    }
  };

  return (
    <fieldset
      className={`relative bg-base-200 border border-base-300 rounded-xl p-4 transition-all duration-300 ${className}`}
      role="search"
      aria-live="polite"
    >
      <legend className="px-2 text-sm font-medium text-base-content/70">
        {t('title') || 'جستجو'}
      </legend>
      <div className="flex items-center gap-2 bg-base-100 border border-base-300 rounded-lg overflow-hidden w-full focus-within:ring-2 focus-within:ring-primary">
        <input
          ref={inputRef}
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          onKeyDown={handleKeyDown}
          placeholder={t('placeholder') || 'جستجوی اخبار...'}
          className="flex-1 py-2.5 px-4 border-none bg-transparent focus:outline-none placeholder:text-base-content/50 disabled:opacity-50 text-base-content"
          aria-label={t('placeholder') || 'جستجوی اخبار...'}
          disabled={isLoading}
        />
        {query && (
          <button
            type="button"
            onClick={handleClear}
            className="p-2 text-base-content/50 hover:text-base-content transition-colors transform hover:scale-110"
            aria-label={t('clear') || 'پاک کردن'}
          >
            <X className="w-5 h-5" />
          </button>
        )}
        <button
          type="button"
          onClick={() => handleSearch()}
          className="p-2.5 text-base-content hover:bg-primary hover:text-primary-content active:bg-primary-dark transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-110"
          aria-label={t('button') || 'جستجو'}
          disabled={isLoading || !query.trim() || query.trim().length < 3}
        >
          {isLoading ? (
            <Loader2 className="animate-spin w-5 h-5" />
          ) : (
            <Search className="w-5 h-5" />
          )}
        </button>
      </div>
    </fieldset>
  );
}