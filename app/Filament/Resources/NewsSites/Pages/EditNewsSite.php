<?php

namespace App\Filament\Resources\NewsSites\Pages;

use App\Filament\Resources\NewsSites\NewsSiteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;

class EditNewsSite extends EditRecord
{
    use Translatable;
    protected static string $resource = NewsSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
