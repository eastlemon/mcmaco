<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartDropdown extends Component
{
    public int $itemsCount = 0;
    public int $total = 0;
    public array $items = [];

    protected $listeners = ['cart-updated' => 'refreshStats'];

    public function mount(CartService $service): void
    {
        $this->computeStats($service);
    }

    public function refreshStats(): void
    {
        $this->computeStats(app(CartService::class));
    }

    public function removeItem(int $itemId): void
    {
        $service = app(CartService::class);
        $cart = $service->getOrCreateCart(request());

        $cart->items()->where('id', $itemId)->delete();

        $this->refreshStats();
        $this->dispatch('cart-updated');
        $this->dispatch('cart-item-removed', itemId: $itemId);
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

        $this->items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'ad_id' => $item->ad_id,
                'name' => $item->ad->title,
                'price' => $item->ad->price,
                'quantity' => $item->qty,
                'subtotal' => $item->ad->price * $item->qty,
                'image' => $item->ad->images->first()?->path ?? null,
                'slug' => $item->ad->slug ?? null,
            ];
        })->toArray();
    }
}
