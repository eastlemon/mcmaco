<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartDropdown extends Component
{
    public int $itemsCount = 0;
    public int $total = 0;

    protected $listeners = ['cart-updated' => 'refreshStats'];

    public function mount(CartService $service): void
    {
        $this->computeStats($service);
    }

    public function refreshStats(): void
    {
        $this->computeStats(app(CartService::class));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.cart-dropdown');
    }

    private function computeStats(CartService $service): void
    {
        $cart = $service->getOrCreateCart(request());
        $items = $cart->items()->with('ad')->get();
        $this->itemsCount = $items->sum('qty');
        $this->total = $items->sum(fn ($i) => $i->ad->price * $i->qty);
    }
}