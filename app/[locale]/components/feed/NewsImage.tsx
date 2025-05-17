'use client';

import Image from 'next/image';
import { useState } from 'react';
import { getAbsoluteUrl } from '@/lib/utils';

interface NewsImageProps {
  src: string;
  alt: string;
  isCover?: boolean;
  className?: string;
  loading?: 'lazy' | 'eager';
}

export default function NewsImage({
  src,
  alt,
  isCover = false,
  className = '',
  loading = 'lazy',
}: NewsImageProps) {
  const [imageSrc, setImageSrc] = useState<string>(() => {
    if (!src || src === 'undefined' || src === 'null') {
      return '/placeholder.png';
    }
    const absoluteUrl = getAbsoluteUrl(
      src,
      process.env.NEXT_PUBLIC_BASE_IMAGE_URL || 'https://core.journa.ir/storage'
    );
    return absoluteUrl && absoluteUrl !== 'undefined' && absoluteUrl !== 'null'
      ? absoluteUrl
      : '/placeholder.png';
  });

  return (
    <div
      className={
        isCover
          ? `w-full h-96 relative rounded-lg mb-6 bg-gray-100 flex items-center justify-center overflow-hidden ${className}`
          : `flex flex-col items-center ${className}`
      }
    >
      <Image
        src={imageSrc}
        alt={alt || 'News image'}
        fill={isCover}
        width={isCover ? undefined : 40}
        height={isCover ? undefined : 40}
        className={isCover ? 'object-cover rounded-lg' : 'object-contain'}
        placeholder={isCover ? 'blur' : undefined}
        blurDataURL={isCover ? '/placeholder.png' : undefined}
        sizes={isCover ? '(max-width: 768px) 100vw, 800px' : undefined}
        style={isCover ? undefined : { background: 'transparent', objectFit: 'contain' }}
        loading={loading}
        onError={() => setImageSrc('/placeholder.png')}
      />
    </div>
  );
}