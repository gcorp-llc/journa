<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsSiteCategoryResource\Pages;
use App\Filament\Resources\NewsSiteCategoryResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Category;
use App\Models\NewsSite;
use App\Models\NewsSiteCategory;

class NewsSiteCategoryResource extends Resource
{
    protected static ?string $model = NewsSiteCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'لینک دسته بندی اخبار';

    protected static ?string $pluralLabel = 'لینک دسته بندی اخبار';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('news_site_id')
                    ->label('News Site')
                    ->options(NewsSite::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(Category::all()->pluck('title', 'id'))
                    ->searchable(),

                Forms\Components\TextInput::make('category_url')
                    ->label('Category Site Url')->unique(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('news.name'),
                Tables\Columns\TextColumn::make('category.title'),
                Tables\Columns\TextColumn::make('category_url'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListNewsSiteCategories::route('/'),
            'create' => Pages\CreateNewsSiteCategory::route('/create'),
            'edit' => Pages\EditNewsSiteCategory::route('/{record}/edit'),
        ];
    }
}
