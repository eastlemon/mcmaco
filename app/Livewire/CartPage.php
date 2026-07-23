<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartPage extends Component
{
    public function incrementQty(int $adId, CartService $service): void
    {
        $cart = $service->getOrCreateCart(request());
        $item = $cart->items()->where('ad_id', $adId)->firstOrFail();
        $service->updateQty($cart, $adId, $item->qty + 1);
        $this->dispatch('cart-updated');
    }

    public function decrementQty(int $adId, CartService $service): void
    {
        $cart = $service->getOrCreateCart(request());
        $item = $cart->items()->where('ad_id', $adId)->firstOrFail();
        $service->updateQty($cart, $adId, $item->qty - 1);
        $this->dispatch('cart-updated');
    }

    public function removeItem(int $adId, CartService $service): void
    {
        $cart = $service->getOrCreateCart(request());
        $service->remove($cart, $adId);
        $this->dispatch('cart-updated');
    }

    public function render(CartService $service): \Illuminate\Contracts\View\View
    {
        $cart = $service->getOrCreateCart(request());
        $items = $cart->items()->with('ad.images', 'ad.category')->get();

        return view('livewire.cart-page', [
            'items' => $items,
            'total' => $cart->total,
        ]);
    }
}