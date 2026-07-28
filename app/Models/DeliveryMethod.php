<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryMethod extends Model
{
    public const TYPE_PICKUP = 'pickup';
    public const TYPE_COURIER = 'courier';
    public const TYPE_WAREHOUSE = 'warehouse'; // ПВЗ (СДЭК, Boxberry)

    public const TYPES = [
        self::TYPE_PICKUP => 'Самовывоз',
        self::TYPE_COURIER => 'Курьер',
        self::TYPE_WAREHOUSE => 'Пункт выдачи',
    ];

    protected $fillable = [
        'code',
        'name',
        'type',
        'base_price',
        'price_per_kg',
        'is_active',
        'sort_order',
        'tracking_url',
        'description',
    ];

    protected $casts = [
        'base_price' => 'integer',
        'price_per_kg' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getFormattedBasePriceAttribute(): string
    {
        return number_format($this->base_price, 0, ',', ' ') . ' ₽';
    }
}