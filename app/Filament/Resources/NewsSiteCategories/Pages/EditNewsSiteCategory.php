<?php

namespace App\Filament\Resources\NewsSiteCategories\Pages;

use App\Filament\Resources\NewsSiteCategories\NewsSiteCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
//use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
//use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;

class EditNewsSiteCategory extends EditRecord
{
//    use Translatable;
    protected static string $resource = NewsSiteCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
