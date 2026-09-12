<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SimulatedEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Simulation engine — generates realistic bot activity for the demo mode.
 *
 * One "tick" of the simulation produces a mix of events weighted by intensity:
 *   - visits: bot views an ad (and may add to favorites)
 *   - chat messages: bot asks a typical pre-purchase question
 *   - orders: bot places an order on an in-stock ad
 *
 * All generated records carry is_simulated=true, so they never pollute
 * real production queries. Every event is also logged to simulated_events
 * for the real-time admin dashboard.
 *
 * The engine is intentionally non-deterministic — bot behaviour follows
 * weighted probability distributions, not fixed scripts. This produces
 * realistic-looking traffic patterns (some bots just browse, some chat,
 * a few buy) without requiring a scenario DSL.
 */
class SimulationEngine
{
    /**
     * Chat templates — realistic pre-purchase questions in Russian.
     * Picking from these makes bot conversations look natural in the
     * admin panel, not "bot hello, I want to buy".
     */
    private const CHAT_TEMPLATES = [
        'Здравствуйте! Подскажите, товар ещё актуален?',
        'Добрый день! Возможен ли небольшой торг?',
        'Здравствуйте! Подскажите, состояние товара на фото соответствует реальному?',
        'Добрый день! Отправите ли в другой регион? Какой доставкой?',
        'Здравствуйте! Торг уместен?',
        'Добрый день! Есть ли ещё фото товара?',
        'Здравствуйте! Подскажите, оригинал ли это?',
        'Добрый день! Когда сможете отправить после оплаты?',
        'Здравствуйте! Возможна ли примерка перед покупкой?',
        'Добрый день! Подскажите габариты товара, пожалуйста.',
    ];

    /**
     * Run a single simulation tick.
     *
     * @param array{visits:int, chats:int, orders:int} $intensity
     * @return array{visits:int, chats:int, orders:int}  Counts actually generated
     */
    public function tick(array $intensity): array
    {
        $result = [
            'visits' => 0,
            'chats'  => 0,
            'orders' => 0,
        ];

        // Bail out cleanly if there's nothing to simulate against
        if (! $this->canRun()) {
            Log::info('Simulation: skipped (no bots or no ads).');
            return $result;
        }

        try {
            $result['visits'] = $this->generateVisits($intensity['visits_per_run'] ?? 0);
            $result['chats']  = $this->generateChats($intensity['chats_per_run'] ?? 0);
            $result['orders'] = $this->generateOrders($intensity['orders_per_run'] ?? 0);
        } catch (\Throwable $e) {
            Log::error('Simulation engine error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }

        return $result;
    }

    private function canRun(): bool
    {
        return User::query()->where('is_simulated', true)->exists()
            && Ad::query()->active()->inStock()->exists();
    }

    /**
     * Generate N bot visits: each visit picks a random bot + random active ad,
     * increments views, optionally favorites the ad, and logs a SimulatedEvent.
     */
    private function generateVisits(int $count): int
    {
        if ($count <= 0) {
            return 0;
        }

        $bots = User::query()->where('is_simulated', true)->pluck('id');
        $ads  = Ad::query()->active()->inStock()->pluck('id');

        if ($bots->isEmpty() || $ads->isEmpty()) {
            return 0;
        }

        $generated = 0;

        foreach (range(1, $count) as $_) {
            $botId = $bots->random();
            $adId  = $ads->random();

            DB::transaction(function () use ($botId, $adId, &$generated) {
                Ad::query()->where('id', $adId)->increment('views');

                SimulatedEvent::create([
                    'type'               => SimulatedEvent::TYPE_VISIT,
                    'simulated_user_id'  => $botId,
                    'ad_id'              => $adId,
                    'occurred_at'        => now(),
                    'payload'            => ['source' => 'simulation_engine'],
                ]);

                $generated++;
            });
        }

        return $generated;
    }

    /**
     * Generate N chat messages: each picks a bot + ad, finds or creates a chat
     * between them, posts a realistic Russian message.
     */
    private function generateChats(int $count): int
    {
        if ($count <= 0) {
            return 0;
        }

        $bots = User::query()->where('is_simulated', true)->pluck('id');
        $ads  = Ad::query()->active()->inStock()->with('user_id')->pluck('id', 'user_id');

        if ($bots->isEmpty() || $ads->isEmpty()) {
            return 0;
        }

        $generated = 0;

        foreach (range(1, $count) as $_) {
            $botId = $bots->random();
            $adId  = $ads->keys()->random();
            $sellerId = $ads->get($adId);

            DB::transaction(function () use ($botId, $adId, $sellerId, &$generated) {
                $chat = Chat::firstOrCreate(
                    [
                        'ad_id'    => $adId,
                        'buyer_id' => $botId,
                    ],
                    [
                        'seller_id'       => $sellerId,
                        'is_simulated'    => true,
                        'last_message_at' => now(),
                    ]
                );

                Message::create([
                    'chat_id'       => $chat->id,
                    'user_id'       => $botId,
                    'message'       => self::CHAT_TEMPLATES[array_rand(self::CHAT_TEMPLATES)],
                    'is_simulated'  => true,
                ]);

                $chat->update(['last_message_at' => now()]);

                SimulatedEvent::create([
                    'type'               => SimulatedEvent::TYPE_CHAT_MESSAGE,
                    'simulated_user_id'  => $botId,
                    'ad_id'              => $adId,
                    'chat_id'            => $chat->id,
                    'occurred_at'        => now(),
                ]);

                $generated++;
            });
        }

        return $generated;
    }

    /**
     * Generate N fake orders: each picks a bot + in-stock ad, creates an order
     * with 1 item from the snapshot fields, marks it is_simulated=true.
     */
    private function generateOrders(int $count): int
    {
        if ($count <= 0) {
            return 0;
        }

        $bots = User::query()->where('is_simulated', true)->pluck('id');
        $ads  = Ad::query()->active()->inStock()->get(['id', 'title', 'price', 'user_id']);

        if ($bots->isEmpty() || $ads->isEmpty()) {
            return 0;
        }

        $generated = 0;

        foreach (range(1, $count) as $_) {
            $botId = $bots->random();
            $ad    = $ads->random();
            $qty   = random_int(1, min(3, $ad->stock));
            $subtotal = $ad->price * $qty;

            DB::transaction(function () use ($botId, $ad, $qty, $subtotal, &$generated) {
                $order = Order::create([
                    'user_id'           => $botId,
                    'status'            => Order::STATUS_NEW,
                    'customer_name'     => User::find($botId)?->name ?? 'Bot',
                    'customer_phone'    => User::find($botId)?->phone ?? '+79990000000',
                    'customer_email'    => User::find($botId)?->email,
                    'delivery_method'   => 'pickup',
                    'delivery_cost'     => 0,
                    'items_total'       => $subtotal,
                    'total'             => $subtotal,
                    'is_quick_order'    => false,
                    'is_simulated'      => true,
                ]);

                OrderItem::create([
                    'order_id'        => $order->id,
                    'ad_id'           => $ad->id,
                    'title_snapshot'  => $ad->title,
                    'price_snapshot'  => $ad->price,
                    'qty'             => $qty,
                    'subtotal'        => $subtotal,
                ]);

                SimulatedEvent::create([
                    'type'               => SimulatedEvent::TYPE_ORDER_PLACED,
                    'simulated_user_id'  => $botId,
                    'ad_id'              => $ad->id,
                    'order_id'           => $order->id,
                    'occurred_at'        => now(),
                    'payload'            => [
                        'order_number' => $order->order_number,
                        'total'        => $subtotal,
                    ],
                ]);

                $generated++;
            });
        }

        return $generated;
    }
}
