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
                Section::make(__('filament.ads.sections.main'))
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('—')
                            ->hint(__('filament.ads.hints.no_owner'))
                            ->label(__('filament.ads.fields.owner')),
                        TextInput::make('title')
                            ->label(__('filament.ads.fields.title'))
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state) . '-' . \Illuminate\Support\Str::random(6))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label(__('filament.ads.fields.description'))
                            ->required()
                            ->maxLength(5000)
                            ->rows(6)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('filament.ads.sections.photos'))
                    ->description(__('filament.ads.hints.photos'))
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

                Section::make(__('filament.ads.sections.price'))
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
                            ->label(__('filament.ads.fields.stock')),
                        TextInput::make('sku')
                            ->label(__('filament.ads.fields.sku'))
                            ->unique(ignoreRecord: true),
                    ])->columns(3),

                Section::make(__('filament.ads.sections.category'))
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),
                        TextInput::make('city'),
                        Select::make('condition')
                            ->options([
                                'new' => __('filament.ads.condition.new'),
                                'used' => __('filament.ads.condition.used'),
                            ])
                            ->required(),
                        Select::make('status')
                            ->options([
                                'pending' => __('filament.ads.status.pending'),
                                'active' => __('filament.ads.status.active'),
                                'closed' => __('filament.ads.status.closed'),
                                'rejected' => __('filament.ads.status.rejected'),
                            ])
                            ->required()
                            ->default('active'),
                        Toggle::make('is_featured')
                            ->label(__('filament.ads.fields.featured'))
                            ->default(false),
                    ])->columns(2),

                Section::make('SEO')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->placeholder(__('filament.ads.placeholders.slug')),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->placeholder(__('filament.ads.placeholders.meta_description')),
                    ])->columns(1),
            ]);
    }
}