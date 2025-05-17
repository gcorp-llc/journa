import { useTranslations } from 'next-intl';
import { AlertTriangle } from 'lucide-react';

interface AlertErrorProps {
  message: string;
}

export default function AlertError({ message }: AlertErrorProps) {
  const t = useTranslations('Error');

  return (
    <div className="flex items-center justify-center min-h-[200px]">
      <div className="bg-error text-error-content p-4 rounded-box shadow-lg max-w-md w-full flex items-center gap-3 opacity-70 glass">
        <AlertTriangle className="w-6 h-6 flex-shrink-0" />
        <div>
          <h3 className="font-bold">{t('title') || 'Error'}</h3>
          <p>{message}</p>
        </div>
      </div>
    </div>
  );
}