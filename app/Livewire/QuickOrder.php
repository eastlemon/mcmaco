<?php

namespace App\Livewire;

use App\Models\Ad;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\NewOrderAdmin;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Validate;
use Livewire\Component;

class QuickOrder extends Component
{
    public int $adId;
    public bool $showModal = false;

    #[Validate('required|string|min:2')]
    public string $name = '';

    #[Validate('required|string|regex:/^[\d\s\+\-\(\)]{10,18}$/')]
    public string $phone = '';

    public function submit(): void
    {
        $this->validate();

        $ad = Ad::active()->inStock()->findOrFail($this->adId);

        $order = Order::create([
            'customer_name' => $this->name,
            'customer_phone' => $this->phone,
            'status' => Order::STATUS_NEW,
            'is_quick_order' => true,
            'items_total' => $ad->price,
            'total' => $ad->price,
            'delivery_method' => 'pickup',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'ad_id' => $ad->id,
            'title_snapshot' => $ad->title,
            'price_snapshot' => $ad->price,
            'qty' => 1,
            'subtotal' => $ad->price,
        ]);

        // Notify admins about new quick order
        $adminEmail = config('mail.admin_address');
        if ($adminEmail) {
            Notification::route('mail', $adminEmail)->notify(new NewOrderAdmin($order));
        }

        $this->showModal = false;
        $this->reset(['name', 'phone']);
        session()->flash('quick-order-success', "Заказ {$order->order_number} создан! Мы свяжемся с вами для подтверждения.");
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.quick-order');
    }
}