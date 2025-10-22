<?php

namespace App\Filament\Resources\NewsSiteCategories\Pages;

use App\Filament\Resources\NewsSiteCategories\NewsSiteCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;

class ListNewsSiteCategories extends ListRecords
{
    use Translatable;
    protected static string $resource = NewsSiteCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
