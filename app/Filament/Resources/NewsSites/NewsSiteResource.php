<?php

namespace App\Filament\Resources\NewsSites;

use App\Filament\Resources\NewsSites\Pages\CreateNewsSite;
use App\Filament\Resources\NewsSites\Pages\EditNewsSite;
use App\Filament\Resources\NewsSites\Pages\ListNewsSites;
use App\Filament\Resources\NewsSites\Schemas\NewsSiteForm;
use App\Filament\Resources\NewsSites\Tables\NewsSitesTable;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use App\Models\NewsSite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NewsSiteResource extends Resource
{
    use Translatable;
    protected static ?string $model = NewsSite::class;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationLabel = 'خبرگزاری ها';

    protected static ?string $pluralLabel = 'خبرگزاری ها';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    public static function form(Schema $schema): Schema
    {
        return NewsSiteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsSitesTable::configure($table);
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
            'index' => ListNewsSites::route('/'),
            'create' => CreateNewsSite::route('/create'),
            'edit' => EditNewsSite::route('/{record}/edit'),
        ];
    }
}
