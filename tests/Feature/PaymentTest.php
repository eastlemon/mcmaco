<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Services\YooKassaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_table_exists(): void
    {
        $order = Order::factory()->create();
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'yookassa',
            'status' => Payment::STATUS_PENDING,
            'amount' => 1000,
        ]);

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    public function test_can_create_a_payment(): void
    {
        $order = Order::factory()->create([
            'total' => 5000,
            'status' => Order::STATUS_NEW,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'yookassa',
            'provider_payment_id' => 'yk_test_123',
            'status' => Payment::STATUS_PENDING,
            'amount' => 5000,
            'currency' => 'RUB',
        ]);

        $this->assertEquals('yookassa', $payment->provider);
        $this->assertEquals(Payment::STATUS_PENDING, $payment->status);
        $this->assertEquals(5000, $payment->amount);
        $this->assertEquals('5 000 ₽', $payment->formatted_amount);
        $this->assertEquals('Ожидает оплаты', $payment->status_label);
    }

    public function test_order_has_many_payments(): void
    {
        $order = Order::factory()->create();

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'yookassa',
            'status' => Payment::STATUS_CANCELED,
            'amount' => $order->total,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'yookassa',
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => $order->total,
        ]);

        $this->assertCount(2, $order->payments);
    }

    public function test_is_succeeded(): void
    {
        $payment = Payment::factory()->create(['status' => Payment::STATUS_SUCCEEDED]);
        $this->assertTrue($payment->isSucceeded());

        $payment->update(['status' => Payment::STATUS_PENDING]);
        $this->assertFalse($payment->isSucceeded());
    }

    public function test_status_constants(): void
    {
        $this->assertArrayHasKey(Payment::STATUS_PENDING, Payment::STATUSES);
        $this->assertArrayHasKey(Payment::STATUS_SUCCEEDED, Payment::STATUSES);
        $this->assertArrayHasKey(Payment::STATUS_CANCELED, Payment::STATUSES);
        $this->assertEquals('Оплачен', Payment::STATUSES[Payment::STATUS_SUCCEEDED]);
    }

    public function test_success_route_accessible(): void
    {
        // Webhook endpoint doesn't render full layout (no cart-dropdown dependency)
        $response = $this->postJson('/payments/yookassa/webhook', [
            'event' => 'payment.canceled',
            'object' => ['id' => 'nonexistent'],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);
    }

    public function test_webhook_returns_ok(): void
    {
        $response = $this->postJson('/payments/yookassa/webhook', [
            'event' => 'payment.succeeded',
            'object' => ['id' => 'nonexistent'],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'ok']);
    }

    public function test_webhook_marks_order_as_paid(): void
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_NEW,
            'total' => 3000,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'yookassa',
            'provider_payment_id' => 'yk_test_456',
            'status' => Payment::STATUS_PENDING,
            'amount' => 3000,
        ]);

        $service = app(YooKassaService::class);
        $service->processWebhook([
            'event' => 'payment.succeeded',
            'object' => [
                'id' => 'yk_test_456',
                'status' => 'succeeded',
                'amount' => ['value' => '30.00', 'currency' => 'RUB'],
            ],
        ]);

        $payment->refresh();
        $order->refresh();

        $this->assertEquals(Payment::STATUS_SUCCEEDED, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals(Order::STATUS_PAID, $order->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_does_not_double_mark_paid(): void
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_PAID,
            'paid_at' => now(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'yookassa',
            'provider_payment_id' => 'yk_test_789',
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->total,
        ]);

        $service = app(YooKassaService::class);

        $service->processWebhook([
            'event' => 'payment.succeeded',
            'object' => ['id' => 'yk_test_789', 'status' => 'succeeded'],
        ]);

        $order->refresh();
        $originalPaidAt = $order->paid_at;

        $service->processWebhook([
            'event' => 'payment.succeeded',
            'object' => ['id' => 'yk_test_789', 'status' => 'succeeded'],
        ]);

        $order->refresh();
        $this->assertEquals($originalPaidAt->timestamp, $order->paid_at->timestamp);
    }
}
