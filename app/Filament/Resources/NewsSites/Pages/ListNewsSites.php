<?php

namespace App\Filament\Resources\NewsSites\Pages;

use App\Filament\Resources\NewsSites\NewsSiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;

class ListNewsSites extends ListRecords
{
    use Translatable;
    protected static string $resource = NewsSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
