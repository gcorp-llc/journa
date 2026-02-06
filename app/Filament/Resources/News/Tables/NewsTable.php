<?php

namespace App\Filament\Resources\News\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')
                    ->label(__('Image'))
                    ->circular()
                    ->disk('public'),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->getTranslation('title', app()->getLocale())),
                TextColumn::make('newsSite.name')
                    ->label(__('News Site'))
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('views')
                    ->label(__('Views'))
                    ->numeric()
                    ->sortable()
                    ->badge(),
                TextColumn::make('published_at')
                    ->label(__('Published At'))
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
