<?php

namespace App\Filament\Resources\NewsSiteResource\Pages;

use App\Filament\Resources\NewsSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewsSites extends ListRecords
{
    use ListRecords\Concerns\Translatable;
    protected static string $resource = NewsSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
