<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdvertisementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('cover')
                    ->image()
                    ->imageEditor(),
                Textarea::make('title')
                    ->label('عنوان')
                    ->required(),
                Textarea::make('subject')
                    ->label('توضیحات')
                    ->required()
                ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('محتوا')
                    ->required()
                    ->columnSpanFull(10),
                TextInput::make('destination_url')
                    ->label('لینک مقصد')
                    ->url()
                    ->maxLength(255),
                DateTimePicker::make('start_date')
                    ->label('تاریخ شروع')
                    ->nullable(),
                DateTimePicker::make('end_date')
                    ->label('تاریخ پایان')
                    ->nullable()
                    ->afterOrEqual('start_date'),
                TextInput::make('max_impressions')
                    ->label('حداکثر نمایش')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                TextInput::make('max_clicks')
                    ->label('حداکثر کلیک')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                TextInput::make('current_impressions')
                    ->label('تعداد نمایش فعلی')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->disabled(),
                TextInput::make('current_clicks')
                    ->label('تعداد کلیک فعلی')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->disabled(),
                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ]);
    }
}
