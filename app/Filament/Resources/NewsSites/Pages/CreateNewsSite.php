<?php

namespace App\Filament\Resources\NewsSites\Pages;

use App\Filament\Resources\NewsSites\NewsSiteResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;

class CreateNewsSite extends CreateRecord
{
    use Translatable;
    protected static string $resource = NewsSiteResource::class;
    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            // ...
        ];
    }
}
