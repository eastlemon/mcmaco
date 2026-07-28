<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Notifications\NewOrderAdmin;
use App\Notifications\OrderCreated;
use App\Notifications\OrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_sends_status_changed_notification(): void
    {
        $order = Order::create([
            'customer_name' => 'Тест Заказа',
            'customer_phone' => '+7 999 111 22 33',
            'customer_email' => 'test@order.ru',
            'status' => Order::STATUS_NEW,
            'items_total' => 5000,
            'total' => 5000,
            'delivery_method' => 'pickup',
        ]);

        $order->update(['status' => Order::STATUS_CONFIRMED]);

        Notification::assertSentTo(
            $order,
            OrderStatusChanged::class
        );
    }

    public function test_does_not_send_status_notification_on_creation(): void
    {
        $order = Order::create([
            'customer_name' => 'Новый Заказ',
            'customer_phone' => '+7 999 555 66 77',
            'customer_email' => 'new@order.ru',
            'status' => Order::STATUS_NEW,
            'items_total' => 3000,
            'total' => 3000,
            'delivery_method' => 'pickup',
        ]);

        Notification::assertNotSentTo(
            $order,
            OrderStatusChanged::class
        );
    }

    public function test_does_not_send_status_notification_without_email(): void
    {
        $order = Order::create([
            'customer_name' => 'Без Мыла Статус',
            'customer_phone' => '+7 999 777 88 99',
            'status' => Order::STATUS_NEW,
            'items_total' => 1000,
            'total' => 1000,
            'delivery_method' => 'pickup',
        ]);

        $order->update(['status' => Order::STATUS_PROCESSING]);

        Notification::assertNotSentTo(
            $order,
            OrderStatusChanged::class
        );
    }

    public function test_status_notification_contains_correct_transitions(): void
    {
        $order = Order::create([
            'customer_name' => 'Переходы',
            'customer_phone' => '+7 999 222 33 44',
            'customer_email' => 'transitions@test.ru',
            'status' => Order::STATUS_NEW,
            'items_total' => 2000,
            'total' => 2000,
            'delivery_method' => 'cdek',
            'delivery_address' => 'Москва',
        ]);

        $order->update(['status' => Order::STATUS_PAID]);
        Notification::assertSentTo($order, OrderStatusChanged::class);

        // Reset and change again
        Notification::fake();
        $order->update(['status' => Order::STATUS_SHIPPED]);
        Notification::assertSentTo($order, OrderStatusChanged::class);
    }

    public function test_order_created_notification_can_be_sent(): void
    {
        $order = Order::create([
            'customer_name' => 'Заказ Покупатель',
            'customer_phone' => '+7 999 444 55 66',
            'customer_email' => 'buyer@test.ru',
            'status' => Order::STATUS_NEW,
            'items_total' => 7500,
            'total' => 7500,
            'delivery_method' => 'post',
        ]);

        $order->notify(new OrderCreated($order));

        Notification::assertSentTo($order, OrderCreated::class);
    }

    public function test_new_order_admin_notification_can_be_sent(): void
    {
        $order = Order::create([
            'customer_name' => 'Админ Тест',
            'customer_phone' => '+7 999 666 77 88',
            'status' => Order::STATUS_NEW,
            'is_quick_order' => true,
            'items_total' => 4200,
            'total' => 4200,
            'delivery_method' => 'pickup',
        ]);

        Notification::route('mail', config('mail.admin_address'))
            ->notify(new NewOrderAdmin($order));

        Notification::assertSentTo(
            Notification::route('mail', config('mail.admin_address')),
            NewOrderAdmin::class
        );
    }
}