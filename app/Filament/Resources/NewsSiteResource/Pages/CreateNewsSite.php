<?php

namespace App\Filament\Resources\NewsSiteResource\Pages;

use App\Filament\Resources\NewsSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsSite extends CreateRecord
{
    protected static string $resource = NewsSiteResource::class;
    use CreateRecord\Concerns\Translatable;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            // ...
        ];
    }
}
