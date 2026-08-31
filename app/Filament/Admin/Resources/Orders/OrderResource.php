<?php

namespace App\Filament\Admin\Resources\Orders;

use App\Filament\Admin\Resources\Orders\Pages\ListOrders;
use App\Filament\Admin\Resources\Orders\Pages\ViewOrder;
use App\Filament\Admin\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    public static function getNavigationLabel(): string
    {
        return __('filament.orders.nav');
    }

    public static function getModelLabel(): string
    {
        return __('filament.orders.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.orders.plural');
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}