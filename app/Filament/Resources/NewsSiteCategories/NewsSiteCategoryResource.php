<?php

namespace App\Filament\Resources\NewsSiteCategories;

use App\Filament\Resources\NewsSiteCategories\Pages\CreateNewsSiteCategory;
use App\Filament\Resources\NewsSiteCategories\Pages\EditNewsSiteCategory;
use App\Filament\Resources\NewsSiteCategories\Pages\ListNewsSiteCategories;
use App\Filament\Resources\NewsSiteCategories\Schemas\NewsSiteCategoryForm;
use App\Filament\Resources\NewsSiteCategories\Tables\NewsSiteCategoriesTable;
use App\Models\NewsSiteCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
//use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;

class NewsSiteCategoryResource extends Resource
{
//    use Translatable;
    protected static ?string $model = NewsSiteCategory::class;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $navigationLabel = 'لینک دسته بندی اخبار';

    protected static ?string $pluralLabel = 'لینک دسته بندی اخبار';

    public static function form(Schema $schema): Schema
    {
        return NewsSiteCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsSiteCategoriesTable::configure($table);
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
            'index' => ListNewsSiteCategories::route('/'),
            'create' => CreateNewsSiteCategory::route('/create'),
            'edit' => EditNewsSiteCategory::route('/{record}/edit'),
        ];
    }
}
