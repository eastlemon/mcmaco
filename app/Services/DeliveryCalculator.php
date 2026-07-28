<?php

namespace App\Services;

use App\Models\DeliveryMethod;
use App\Models\Order;

class DeliveryCalculator
{
    /**
     * Calculate delivery cost for an order.
     *
     * @param DeliveryMethod $method
     * @param int $totalWeight Weight in grams (0 if unknown)
     * @return int Price in rubles
     */
    public function calculate(DeliveryMethod $method, int $totalWeight = 0): int
    {
        if ($method->type === DeliveryMethod::TYPE_PICKUP) {
            return 0;
        }

        $cost = $method->base_price;

        // Convert grams to kg for per-kg pricing
        if ($totalWeight > 0 && $method->price_per_kg > 0) {
            $kg = ceil($totalWeight / 1000);
            $cost += $kg * $method->price_per_kg;
        }

        return $cost;
    }

    /**
     * Calculate total weight of an order from its items.
     */
    public function orderWeight(Order $order): int
    {
        return $order->items()
            ->with('ad')
            ->get()
            ->sum(fn ($item) => ($item->ad->weight ?? 0) * $item->qty);
    }

    /**
     * Build tracking URL for an order.
     */
    public function trackingUrl(Order $order): ?string
    {
        if (!$order->tracking_number || !$order->deliveryMethod) {
            return null;
        }

        $template = $order->deliveryMethod->tracking_url;
        if (!$template) {
            return null;
        }

        return str_replace('{track}', $order->tracking_number, $template);
    }
}