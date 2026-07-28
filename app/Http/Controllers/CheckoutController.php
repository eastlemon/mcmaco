<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\NewOrderAdmin;
use App\Notifications\OrderCreated;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class CheckoutController extends Controller
{
    public function index(Request $request, CartService $cartService)
    {
        $cart = $cartService->getOrCreateCart($request);
        $items = $cart->items()->with('ad')->get();

        if ($items->isEmpty()) {
            return redirect()->route('ads.index')->with('error', 'Корзина пуста');
        }

        $total = $items->sum(fn ($item) => $item->ad->price * $item->qty);
        $deliveryMethods = Order::DELIVERY_METHODS;

        return view('checkout.index', compact('items', 'total', 'deliveryMethods'));
    }

    public function store(Request $request, CartService $cartService)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|min:2',
            'customer_phone' => 'required|string|regex:/^[\d\s\+\-\(\)]{10,18}$/',
            'customer_email' => 'nullable|email',
            'delivery_method' => 'required|in:' . implode(',', array_keys(Order::DELIVERY_METHODS)),
            'delivery_address' => 'required_unless:delivery_method,pickup|nullable|string',
            'comment' => 'nullable|string|max:1000',
        ]);

        $cart = $cartService->getOrCreateCart($request);
        $items = $cart->items()->with('ad')->get();

        if ($items->isEmpty()) {
            return redirect()->route('ads.index');
        }

        $itemsTotal = $items->sum(fn ($item) => $item->ad->price * $item->qty);
        $deliveryCost = match ($validated['delivery_method']) {
            'pickup' => 0,
            'cdek' => 350,
            'post' => 250,
            'courier' => 400,
            default => 0,
        };

        $order = Order::create([
            'user_id' => $request->user()?->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? null,
            'delivery_method' => $validated['delivery_method'],
            'delivery_address' => $validated['delivery_address'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'items_total' => $itemsTotal,
            'delivery_cost' => $deliveryCost,
            'total' => $itemsTotal + $deliveryCost,
            'status' => Order::STATUS_NEW,
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'ad_id' => $item->ad_id,
                'title_snapshot' => $item->ad->title,
                'price_snapshot' => $item->ad->price,
                'qty' => $item->qty,
                'subtotal' => $item->ad->price * $item->qty,
            ]);
        }

        $cartService->clear($cart);

        // Email notifications
        if ($order->customer_email) {
            $order->notify(new OrderCreated($order));
        }
        $adminEmail = config('mail.admin_address');
        if ($adminEmail) {
            Notification::route('mail', $adminEmail)->notify(new NewOrderAdmin($order));
        }

        // If payment method is online, redirect to payment
        if ($request->has('pay_online') && app(\App\Services\YooKassaService::class)->isEnabled()) {
            return redirect()->route('payments.pay', $order);
        }

        return redirect()->route('orders.show', $order);
    }

    public function show(Order $order)
    {
        $order->load('items.ad');
        return view('checkout.order', compact('order'));
    }
}