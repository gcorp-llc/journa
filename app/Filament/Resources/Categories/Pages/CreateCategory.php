<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;

class CreateCategory extends CreateRecord
{
    use Translatable;
    protected static string $resource = CategoryResource::class;
    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            // ...
        ];
    }
}
