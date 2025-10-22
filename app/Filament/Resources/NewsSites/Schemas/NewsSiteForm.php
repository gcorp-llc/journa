<?php

namespace App\Filament\Resources\NewsSites\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NewsSiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('site_url'),
                RichEditor::make('description')
                    ->columnSpanFull(),
                FileUpload::make('logo_url')
                    ->image()
                    ->imageEditor(),
            ]);
    }
}
