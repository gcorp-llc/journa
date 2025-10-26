<?php

namespace App\Filament\Resources\NewsSiteCategories\Schemas;

use App\Models\Category;
use App\Models\NewsSite;
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
                    ->label('News Site')
                    ->options(NewsSite::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('category_id')
                    ->label('Category')
                    ->options(Category::query()->pluck('title', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('category_url')
                    ->url()
                    ->required(),
                DateTimePicker::make('last_crawled'),
            ]);
    }
}
