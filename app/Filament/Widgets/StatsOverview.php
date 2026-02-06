<?php

namespace App\Filament\Widgets;

use App\Models\News;
use App\Models\NewsSite;
use App\Models\Advertisement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total News', News::count())
                ->description('All published and draft news')
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('success'),
            Stat::make('News Sites', NewsSite::count())
                ->description('Active news sources')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
            Stat::make('Active Ads', Advertisement::where('is_active', true)->count())
                ->description('Currently running campaigns')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('warning'),
        ];
    }
}
