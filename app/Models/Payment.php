<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'provider',
        'provider_payment_id',
        'status',
        'amount',
        'currency',
        'confirmation_url',
        'payload',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_PENDING => 'Ожидает оплаты',
        self::STATUS_SUCCEEDED => 'Оплачен',
        self::STATUS_CANCELED => 'Отменён',
        self::STATUS_REFUNDED => 'Возврат',
        self::STATUS_FAILED => 'Ошибка',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' ₽';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isSucceeded(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }
}
