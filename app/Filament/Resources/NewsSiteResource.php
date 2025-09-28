<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsSiteResource\Pages;
use App\Filament\Resources\NewsSiteResource\RelationManagers;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\NewsSite;

class NewsSiteResource extends Resource
{
    use Translatable;
    protected static ?string $model = NewsSite::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'خبرگزاری ها';

    protected static ?string $pluralLabel = 'خبرگزاری ها';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name'),
                Forms\Components\TextInput::make('site_url'),
                Forms\Components\RichEditor::make('description')
                ->columnSpanFull(),
                Forms\Components\FileUpload::make('logo_url')
                    ->image()
                    ->imageEditor(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_url'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('site_url'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListNewsSites::route('/'),
            'create' => Pages\CreateNewsSite::route('/create'),
            'edit' => Pages\EditNewsSite::route('/{record}/edit'),
        ];
    }
}
