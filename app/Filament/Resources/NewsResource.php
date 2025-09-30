<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\News;
use App\Models\NewsSite;

class NewsResource extends Resource
{
    use Translatable;

    protected static ?string $model = News::class;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'اخبار';

    protected static ?string $pluralLabel = 'اخبار';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Content'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug'),
                        Forms\Components\FileUpload::make('cover')
                            ->image()
                            ->imageEditor(),
                        Forms\Components\RichEditor::make('content')
                            ->label(__('محتوا'))
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('source_url')
                            ->label(__('Source URL'))
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Select::make('news_site_id')
                            ->label(__('خبرگزاری'))
                            ->options(NewsSite::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('Metadata'))
                    ->schema([
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label(__('Published At'))
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => __('Draft'),
                                'published' => __('Published'),
                                'archived' => __('Archived'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('views')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('Categories'))
                    ->schema([
                        Forms\Components\Select::make('categories')
                            ->relationship('categories', 'title')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->label(__('Categories')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->label(__('تصویر'))
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('عنوان'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'draft' => __('Draft'),
                        'published' => __('Published'),
                        'archived' => __('Archived'),
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories.title')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('newsSite.name')
                    ->label(__('خبرگزاری'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => __('Draft'),
                        'published' => __('Published'),
                        'archived' => __('Archived'),
                    ]),
                Tables\Filters\SelectFilter::make('categories')
                    ->relationship('categories', 'title')
                    ->multiple(),
                Tables\Filters\SelectFilter::make('news_site_id')
                    ->label(__('خبرگزاری'))
                    ->relationship('newsSite', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
