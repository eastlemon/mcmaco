<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'order_number', 'status',
        'customer_name', 'customer_phone', 'customer_email',
        'delivery_address', 'delivery_method', 'delivery_cost',
        'items_total', 'total', 'comment',
        'is_quick_order', 'paid_at',
    ];

    protected $casts = [
        'items_total' => 'integer',
        'delivery_cost' => 'integer',
        'total' => 'integer',
        'is_quick_order' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PAID = 'paid';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_NEW => 'Новый',
        self::STATUS_CONFIRMED => 'Подтверждён',
        self::STATUS_PAID => 'Оплачен',
        self::STATUS_PROCESSING => 'Собирается',
        self::STATUS_SHIPPED => 'Отправлен',
        self::STATUS_DELIVERED => 'Доставлен',
        self::STATUS_DONE => 'Завершён',
        self::STATUS_CANCELLED => 'Отменён',
    ];

    public const DELIVERY_METHODS = [
        'pickup' => 'Самовывоз',
        'cdek' => 'СДЭК',
        'post' => 'Почта России',
        'courier' => 'Курьер',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'MC-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 0, ',', ' ') . ' ₽';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getDeliveryMethodLabelAttribute(): string
    {
        return self::DELIVERY_METHODS[$this->delivery_method] ?? $this->delivery_method;
    }
}