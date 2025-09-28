<?php

namespace App\Filament\Resources\NewsSiteCategoryResource\Pages;

use App\Filament\Resources\NewsSiteCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewsSiteCategory extends EditRecord
{
    protected static string $resource = NewsSiteCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
