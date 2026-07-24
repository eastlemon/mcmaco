<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YooKassaService
{
    private string $shopId;
    private string $secretKey;
    private string $apiBase = 'https://api.yookassa.ru/v3';
    private bool $enabled;

    public function __construct()
    {
        $this->shopId = config('payments.shop_id', '') ?? '';
        $this->secretKey = config('payments.secret_key', '') ?? '';
        $this->enabled = config('payments.enabled', false) ?? false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->shopId) && !empty($this->secretKey);
    }

    /**
     * Create a payment for an order.
     */
    public function createPayment(Order $order): Payment
    {
        $description = strtr(config('payments.description_template'), [
            '{order_number}' => $order->order_number,
            '{site}' => config('app.name', 'mcma.co'),
        ]);

        $idempotenceKey = uniqid('mc_', true);

        $payload = [
            'amount' => [
                'value' => number_format($order->total / 100, 2, '.', ''),
                'currency' => config('payments.currency', 'RUB'),
            ],
            'capture' => true,
            'confirmation' => [
                'type' => config('payments.confirmation', 'redirect'),
                'return_url' => route('payments.success', ['order' => $order]),
            ],
            'description' => $description,
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ];

        $response = $this->client()->post('/payments', $payload, $idempotenceKey);

        if (!$response->successful()) {
            Log::error('YooKassa createPayment failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('YooKassa API error: ' . $response->status());
        }

        $data = $response->json();

        $confirmationUrl = $data['confirmation']['confirmation_url'] ?? null;

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'yookassa',
            'provider_payment_id' => $data['id'] ?? null,
            'status' => $this->mapStatus($data['status'] ?? 'pending'),
            'amount' => $order->total,
            'currency' => config('payments.currency', 'RUB'),
            'confirmation_url' => $confirmationUrl,
            'payload' => $data,
        ]);

        return $payment;
    }

    /**
     * Get payment status from YooKassa and update local record.
     */
    public function checkPaymentStatus(Payment $payment): Payment
    {
        if (!$payment->provider_payment_id) {
            return $payment;
        }

        $response = $this->client()->get("/payments/{$payment->provider_payment_id}");

        if (!$response->successful()) {
            Log::error('YooKassa getPayment failed', [
                'payment_id' => $payment->id,
                'status' => $response->status(),
            ]);
            return $payment;
        }

        $data = $response->json();
        $newStatus = $this->mapStatus($data['status'] ?? 'pending');

        $payment->update([
            'status' => $newStatus,
            'payload' => array_merge($payment->payload ?? [], $data),
            'paid_at' => $newStatus === Payment::STATUS_SUCCEEDED ? now() : $payment->paid_at,
        ]);

        if ($newStatus === Payment::STATUS_SUCCEEDED && !$payment->order->paid_at) {
            $payment->order->update([
                'status' => Order::STATUS_PAID,
                'paid_at' => now(),
            ]);
        }

        return $payment->fresh();
    }

    /**
     * Create a refund for a payment.
     */
    public function refund(Payment $payment, ?int $amount = null): array
    {
        $refundAmount = $amount ?? $payment->amount;
        $idempotenceKey = uniqid('rf_', true);

        $payload = [
            'payment_id' => $payment->provider_payment_id,
            'amount' => [
                'value' => number_format($refundAmount / 100, 2, '.', ''),
                'currency' => $payment->currency,
            ],
        ];

        $response = $this->client()->post('/refunds', $payload, $idempotenceKey);

        if (!$response->successful()) {
            Log::error('YooKassa refund failed', [
                'payment_id' => $payment->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('YooKassa refund error: ' . $response->status());
        }

        $data = $response->json();

        $payment->update([
            'status' => Payment::STATUS_REFUNDED,
            'payload' => array_merge($payment->payload ?? [], ['refund' => $data]),
        ]);

        return $data;
    }

    /**
     * Process incoming webhook from YooKassa.
     */
    public function processWebhook(array $event): void
    {
        $eventData = $event['event'] ?? null;
        $object = $event['object'] ?? [];

        if (!$eventData || empty($object['id'])) {
            Log::warning('YooKassa webhook: malformed event', $event);
            return;
        }

        $payment = Payment::where('provider_payment_id', $object['id'])->first();

        if (!$payment) {
            Log::warning('YooKassa webhook: payment not found', ['provider_payment_id' => $object['id']]);
            return;
        }

        $newStatus = match ($eventData) {
            'payment.succeeded' => Payment::STATUS_SUCCEEDED,
            'payment.canceled' => Payment::STATUS_CANCELED,
            'refund.succeeded' => Payment::STATUS_REFUNDED,
            default => $this->mapStatus($object['status'] ?? 'pending'),
        };

        $payment->update([
            'status' => $newStatus,
            'payload' => array_merge($payment->payload ?? [], $object),
            'paid_at' => $newStatus === Payment::STATUS_SUCCEEDED ? now() : $payment->paid_at,
        ]);

        if ($newStatus === Payment::STATUS_SUCCEEDED && !$payment->order->paid_at) {
            $payment->order->update([
                'status' => Order::STATUS_PAID,
                'paid_at' => now(),
            ]);
        }

        Log::info('YooKassa webhook processed', [
            'payment_id' => $payment->id,
            'event' => $eventData,
            'status' => $newStatus,
        ]);
    }

    private function client(): YooKassaHttpClient
    {
        return new YooKassaHttpClient($this->shopId, $this->secretKey, $this->apiBase);
    }

    private function mapStatus(string $ykStatus): string
    {
        return match ($ykStatus) {
            'succeeded' => Payment::STATUS_SUCCEEDED,
            'canceled' => Payment::STATUS_CANCELED,
            'pending', 'waiting_for_capture' => Payment::STATUS_PENDING,
            default => Payment::STATUS_PENDING,
        };
    }
}
