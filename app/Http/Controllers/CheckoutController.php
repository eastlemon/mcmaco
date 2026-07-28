<?php

namespace App\Http\Controllers;

use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\NewOrderAdmin;
use App\Notifications\OrderCreated;
use App\Services\CartService;
use App\Services\DeliveryCalculator;
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
        $deliveryMethods = DeliveryMethod::active()->get();

        // Pre-calculate delivery cost for each method
        $totalWeight = $items->sum(fn ($item) => ($item->ad->weight ?? 0) * $item->qty);
        $calculator = app(DeliveryCalculator::class);
        $deliveryOptions = $deliveryMethods->map(function ($method) use ($calculator, $totalWeight) {
            return [
                'method' => $method,
                'cost' => $calculator->calculate($method, $totalWeight),
            ];
        });

        return view('checkout.index', compact('items', 'total', 'deliveryOptions'));
    }

    public function store(Request $request, CartService $cartService)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|min:2',
            'customer_phone' => 'required|string|regex:/^[\d\s\+\-\(\)]{10,18}$/',
            'customer_email' => 'nullable|email',
            'delivery_method_id' => 'required|exists:delivery_methods,id',
            'delivery_address' => 'nullable|string',
            'comment' => 'nullable|string|max:1000',
        ]);

        $deliveryMethod = DeliveryMethod::findOrFail($validated['delivery_method_id']);

        // Address required for non-pickup
        if ($deliveryMethod->type !== DeliveryMethod::TYPE_PICKUP && empty($validated['delivery_address'])) {
            return back()->withErrors(['delivery_address' => 'Укажите адрес доставки'])->withInput();
        }

        $cart = $cartService->getOrCreateCart($request);
        $items = $cart->items()->with('ad')->get();

        if ($items->isEmpty()) {
            return redirect()->route('ads.index');
        }

        $itemsTotal = $items->sum(fn ($item) => $item->ad->price * $item->qty);

        $calculator = app(DeliveryCalculator::class);
        $totalWeight = $items->sum(fn ($item) => ($item->ad->weight ?? 0) * $item->qty);
        $deliveryCost = $calculator->calculate($deliveryMethod, $totalWeight);

        $order = Order::create([
            'user_id' => $request->user()?->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? null,
            'delivery_method' => $deliveryMethod->code,
            'delivery_method_id' => $deliveryMethod->id,
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
        $order->load('items.ad', 'deliveryMethod');
        return view('checkout.order', compact('order'));
    }
}