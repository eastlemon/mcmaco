<?php

namespace App\Livewire;

use App\Models\Ad;
use App\Services\CartService;
use Livewire\Component;

class CartDropdown extends Component
{
    public int $itemsCount = 0;
    public int $total = 0;

    protected $listeners = ['cart-updated' => 'updateStats'];

    public function mount(CartService $service): void
    {
        $this->updateStats($service);
    }

    public function updateStats(CartService $service): void
    {
        $cart = $service->getOrCreateCart(request());
        $this->itemsCount = $cart->items_count;
        $this->total = $cart->total;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.cart-dropdown');
    }
}