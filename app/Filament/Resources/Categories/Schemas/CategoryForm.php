<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Guava\IconPicker\Forms\Components\IconPicker;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                IconPicker::make('icon')
                    ->listSearchResults(),
                TextInput::make('title')
                    ->required(),
                Select::make('parent_id')
                    ->relationship('parent', 'title'),

                TextInput::make('slug')
                    ->required(),

                TextInput::make('sort_order')
                    ->numeric(),
                RichEditor::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
