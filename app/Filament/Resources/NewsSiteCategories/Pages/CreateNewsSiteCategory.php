<?php

namespace App\Filament\Resources\NewsSiteCategories\Pages;

use App\Filament\Resources\NewsSiteCategories\NewsSiteCategoryResource;
use Filament\Resources\Pages\CreateRecord;
//use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;
//use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;

class CreateNewsSiteCategory extends CreateRecord
{
//    use Translatable;
    protected static string $resource = NewsSiteCategoryResource::class;
    protected function getHeaderActions(): array
    {
        return [
//            LocaleSwitcher::make(),
            // ...
        ];
    }
}
