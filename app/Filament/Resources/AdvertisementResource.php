<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementResource\Pages;
use App\Filament\Resources\AdvertisementResource\RelationManagers;
use App\Models\Advertisement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class AdvertisementResource extends Resource
{
    use Translatable;
    protected static ?string $model = Advertisement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'تبلیغات';

    protected static ?string $pluralLabel = 'تبلیغات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان')
                    ->required(), // پشتیبانی از ترجمه چندزبانه
                Forms\Components\FileUpload::make('cover')
                    ->image()
                    ->imageEditor(),
                Forms\Components\Textarea::make('subject')
                    ->label('توضیحات')
                    ->required(),
                    Forms\Components\RichEditor::make('content')
                    ->label('محتوا')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('destination_url')
                    ->label('لینک مقصد')
                    ->url()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('start_date')
                    ->label('تاریخ شروع')
                    ->nullable(),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label('تاریخ پایان')
                    ->nullable()
                    ->afterOrEqual('start_date'),
                Forms\Components\TextInput::make('max_impressions')
                    ->label('حداکثر نمایش')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                Forms\Components\TextInput::make('max_clicks')
                    ->label('حداکثر کلیک')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                Forms\Components\TextInput::make('current_impressions')
                    ->label('تعداد نمایش فعلی')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->disabled(),
                Forms\Components\TextInput::make('current_clicks')
                    ->label('تعداد کلیک فعلی')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->disabled(),
                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('موضوع')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاریخ شروع')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاریخ پایان')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_impressions')
                    ->label('نمایش‌ها')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_clicks')
                    ->label('کلیک‌ها')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعال'),
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
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }
}
