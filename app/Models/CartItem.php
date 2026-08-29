<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $qty
 */
class CartItem extends Model
{
    protected $fillable = ['cart_id', 'ad_id', 'qty'];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Cart, $this> */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Ad, $this> */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function getSubtotalAttribute(): int
    {
        return ($this->ad->price ?? 0) * $this->qty;
    }
}