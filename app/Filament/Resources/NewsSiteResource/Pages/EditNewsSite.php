<?php

namespace App\Filament\Resources\NewsSiteResource\Pages;

use App\Filament\Resources\NewsSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewsSite extends EditRecord
{
    use EditRecord\Concerns\Translatable;
    protected static string $resource = NewsSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
