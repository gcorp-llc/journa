'use client';

import { FC } from 'react';

interface SkeletonProps {
  count: number; // تعداد کارت‌های Skeleton
}

const Skeleton: FC<SkeletonProps> = ({ count }) => {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-5">
      {Array.from({ length: count }).map((_, index) => (
        <div key={index} className="flex flex-col gap-2">
          <div className="skeleton h-32 w-full rounded-lg"></div>
          <div className="skeleton h-4 w-28 rounded"></div>
          <div className="skeleton h-4 w-full rounded"></div>
          <div className="skeleton h-4 w-full rounded"></div>
        </div>
      ))}
    </div>
  );
};

export default Skeleton;