<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int    $id
 * @property string $type
 * @property int|null $simulated_user_id
 * @property int|null $ad_id
 * @property int|null $order_id
 * @property int|null $chat_id
 * @property array|null $payload
 * @property Carbon $occurred_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SimulatedEvent extends Model
{
    protected $fillable = [
        'type',
        'simulated_user_id',
        'ad_id',
        'order_id',
        'chat_id',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public const TYPE_VISIT         = 'visit';
    public const TYPE_FAVORITE      = 'favorite';
    public const TYPE_CHAT_MESSAGE  = 'chat_message';
    public const TYPE_ORDER_PLACED  = 'order_placed';
    public const TYPE_ORDER_STATUS  = 'order_status';
    public const TYPE_STOCK_CHANGE  = 'stock_change';

    public const TYPES = [
        self::TYPE_VISIT         => 'Просмотр товара',
        self::TYPE_FAVORITE       => 'Добавление в избранное',
        self::TYPE_CHAT_MESSAGE  => 'Сообщение в чате',
        self::TYPE_ORDER_PLACED  => 'Новый заказ',
        self::TYPE_ORDER_STATUS  => 'Смена статуса заказа',
        self::TYPE_STOCK_CHANGE  => 'Изменение склада',
    ];

    /** @return BelongsTo<User, $this> */
    public function simulatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'simulated_user_id');
    }

    /** @return BelongsTo<Ad, $this> */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Chat, $this> */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /** @param Builder<self> $query */
    public function scopeRecent(Builder $query, int $minutes = 60): Builder
    {
        return $query->where('occurred_at', '>=', now()->subMinutes($minutes));
    }
}
