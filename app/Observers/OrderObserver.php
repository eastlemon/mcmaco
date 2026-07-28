<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function updating(Order $order): void
    {
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;

            // Only notify on meaningful transitions (skip initial creation)
            if ($oldStatus !== $newStatus && $newStatus !== Order::STATUS_NEW) {
                // Notify customer if they have email
                if ($order->customer_email) {
                    try {
                        $order->notify(new OrderStatusChanged($order, $oldStatus));
                    } catch (\Throwable $e) {
                        Log::warning("Failed to send OrderStatusChanged email for order {$order->order_number}: {$e->getMessage()}");
                    }
                }
            }
        }
    }
}