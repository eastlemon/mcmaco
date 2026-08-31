<?php

namespace App\Filament\Admin\Resources\Ads;

use App\Filament\Admin\Resources\Ads\Pages\CreateAd;
use App\Filament\Admin\Resources\Ads\Pages\EditAd;
use App\Filament\Admin\Resources\Ads\Pages\ListAds;
use App\Filament\Admin\Resources\Ads\Schemas\AdForm;
use App\Filament\Admin\Resources\Ads\Tables\AdsTable;
use App\Models\Ad;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return __('filament.ads.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.ads.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return AdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdsTable::configure($table);
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
            'index' => ListAds::route('/'),
            'create' => CreateAd::route('/create'),
            'edit' => EditAd::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
