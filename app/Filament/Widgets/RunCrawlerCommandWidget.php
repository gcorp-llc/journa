<?php

namespace App\Filament\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Artisan;

class RunCrawlerCommandWidget extends Widget
{
    protected static string $view = 'filament.widgets.run-crawler-command-widget';

    // تنظیم عرض ویجت (اختیاری)
    protected int | string | array $columnSpan = 'full';

    // متد برای اجرای دستور
    public function runCommand()
    {
        try {
            // اجرای دستور Artisan (مثلاً یک دستور نمونه به نام report:generate)
            $exitCode = Artisan::call('crawler:run');

            // بررسی نتیجه اجرای دستور
            if ($exitCode === 0) {
                Notification::make()
                    ->title('موفقیت')
                    ->body('دستور با موفقیت اجرا شد!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('خطا')
                    ->body('اجرای دستور با خطا مواجه شد.')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطا')
                ->body('خطایی رخ داد: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
