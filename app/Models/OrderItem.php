<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'ad_id', 'title_snapshot', 'price_snapshot', 'qty', 'subtotal',
    ];

    protected $casts = [
        'price_snapshot' => 'integer',
        'qty' => 'integer',
        'subtotal' => 'integer',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Ad, $this> */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_snapshot, 0, ',', ' ') . ' ₽';
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 0, ',', ' ') . ' ₽';
    }
}