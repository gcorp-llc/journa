<?php

namespace App\Filament\Resources\News\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('News Content'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique('news', 'slug', ignoreRecord: true),

                        FileUpload::make('cover')
                            ->label(__('Cover Image'))
                            ->image()
                            ->directory('content_images/' . now()->format('Y-m-d'))
                            ->imageEditor(),

                        TextInput::make('source_url')
                            ->label(__('Source URL'))
                            ->url()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('Body'))
                    ->schema([
                        RichEditor::make('content')
                            ->label(__('Content'))
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Metadata'))
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->label(__('Published At'))
                            ->default(now())
                            ->required(),

                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived'
                            ])
                            ->default('draft')
                            ->required(),

                        Select::make('news_site_id')
                            ->label(__('News Site'))
                            ->relationship('newsSite', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('views')
                            ->label(__('Views'))
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])->columns(2),
            ]);
    }
}
