<?php

namespace App\Filament\Resources\NewsSiteCategories\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NewsSiteCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('news_site_id')
                    ->relationship('news', 'name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'title')
                    ->required(),

                TextInput::make('category_url')
                    ->url()
                    ->required(),
                DateTimePicker::make('last_crawled'),
            ]);
    }
}
