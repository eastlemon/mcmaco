<?php

namespace App\Livewire;

use App\Models\Ad;
use App\Services\CartService;
use Livewire\Component;

class AddToCart extends Component
{
    public int $adId;
    public string $label = '🛒 В корзину';
    public string $class = 'w-full bg-amber-600 hover:bg-amber-700 text-white font-medium py-3 rounded-lg transition';

    public function addToCart(CartService $service): void
    {
        $cart = $service->getOrCreateCart(request());
        $service->add($cart, $this->adId);
        $this->dispatch('cart-updated')->to(CartDropdown::class);
        $this->dispatch('added-to-cart');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.add-to-cart');
    }
}