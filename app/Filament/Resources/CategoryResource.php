<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;
use Guava\FilamentIconPicker\Tables\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryResource extends Resource
{
    use Translatable;
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationLabel = 'دسته بندی';

    protected static ?string $pluralLabel = 'دسته بندی';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
//                    ->afterStateUpdated(function (Forms\Set $set, $state) {
//                        $set('slug', Str::slug($state));
//                    })
                ,
                Forms\Components\TextInput::make('slug'),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(Category::all()->pluck('title', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\TextInput::make('sort_order')
                    ->label('sort order')
                ->numeric()
                ->nullable(),
//                Forms\Components\Textarea::make('icon')
//                    ->label('icon svg')
//                    ->rows(10)
//                    ->cols(20)
//                    ->nullable()
//                    ->columnSpanFull(),
                IconPicker::make('icon'),
                Forms\Components\Textarea::make('description')
                    ->rows(10)
                    ->cols(20)
                    ->nullable()
                ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order'),
                IconColumn::make('icon'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('parent.title')

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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
