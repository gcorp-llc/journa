<?php

namespace App\Filament\Resources\Advertisements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdvertisementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),
            TextColumn::make('subject')
                ->label('موضوع')
                ->searchable(),
                TextColumn::make('start_date')
                    ->label('تاریخ شروع')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('تاریخ پایان')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('current_impressions')
                    ->label('نمایش‌ها')
                    ->sortable(),
                TextColumn::make('current_clicks')
                    ->label('کلیک‌ها')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
