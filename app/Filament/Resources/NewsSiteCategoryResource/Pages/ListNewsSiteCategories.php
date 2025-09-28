<?php

namespace App\Filament\Resources\NewsSiteCategoryResource\Pages;

use App\Filament\Resources\NewsSiteCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewsSiteCategories extends ListRecords
{
    protected static string $resource = NewsSiteCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
