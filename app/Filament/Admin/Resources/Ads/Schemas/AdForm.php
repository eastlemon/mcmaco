<?php

namespace App\Filament\Admin\Resources\Ads\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->nullable()
                            ->placeholder('—')
                            ->hint('Товары магазина создаются без владельца')
                            ->label('Владелец'),
                        TextInput::make('title')
                            ->label('Название')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state) . '-' . \Illuminate\Support\Str::random(6))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label('Описание')
                            ->required()
                            ->maxLength(5000)
                            ->rows(6)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Фотографии')
                    ->description('До 10 изображений, порядок перетаскиванием — первое становится обложкой')
                    ->schema([
                        FileUpload::make('uploadedImages')
                            ->label('')
                            ->image()
                            ->imageEditor()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('ads/draft')
                            ->visibility('public')
                            ->getUploadedFileNameForStorageUsing(fn ($file): string => (string) Str::uuid() . '.' . $file->getClientOriginalExtension())
                            ->afterStateHydrated(function ($component, ?\App\Models\Ad $record): void {
                                $component->state($record?->images->pluck('path')->all() ?? []);
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Цена и наличие')
                    ->schema([
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('₽'),
                        TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(1)
                            ->label('Остаток на складе'),
                        TextInput::make('sku')
                            ->label('Артикул (SKU)')
                            ->unique(ignoreRecord: true),
                    ])->columns(3),

                Section::make('Категория и параметры')
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),
                        TextInput::make('city'),
                        Select::make('condition')
                            ->options([
                                'new' => 'Новое',
                                'used' => 'Б/у',
                            ])
                            ->required(),
                        Select::make('status')
                            ->options([
                                'pending' => 'На модерации',
                                'active' => 'Активен',
                                'closed' => 'Закрыт',
                                'rejected' => 'Отклонён',
                            ])
                            ->required()
                            ->default('active'),
                        Toggle::make('is_featured')
                            ->label('Рекомендуемый (на главной)')
                            ->default(false),
                    ])->columns(2),

                Section::make('SEO')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->placeholder('Авто из заголовка'),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->placeholder('Авто из описания'),
                    ])->columns(1),
            ]);
    }
}