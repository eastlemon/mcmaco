<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\YooKassaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private YooKassaService $yooKassa,
    ) {}

    /**
     * Initiate payment for an order — redirect to YooKassa.
     */
    public function pay(Order $order, Request $request)
    {
        if (!in_array($order->status, [Order::STATUS_NEW, Order::STATUS_CONFIRMED])) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Заказ уже обработан или отменён.');
        }

        // Check for existing pending payment
        $existing = $order->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('confirmation_url')
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->away($existing->confirmation_url);
        }

        try {
            $payment = $this->yooKassa->createPayment($order);
        } catch (\Throwable $e) {
            Log::error('Failed to create YooKassa payment', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'Не удалось создать платёж. Попробуйте позже или свяжитесь с нами.');
        }

        if ($payment->confirmation_url) {
            return redirect()->away($payment->confirmation_url);
        }

        return redirect()->route('orders.show', $order)
            ->with('error', 'Платёж создан, но способ подтверждения не поддерживается.');
    }

    /**
     * Success return URL — user returns here after payment.
     */
    public function success(Order $order)
    {
        // Check if there's a pending payment to verify
        $pendingPayment = $order->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        if ($pendingPayment && $this->yooKassa->isEnabled()) {
            try {
                $this->yooKassa->checkPaymentStatus($pendingPayment);
            } catch (\Throwable $e) {
                Log::error('Failed to check payment status on success return', [
                    'payment_id' => $pendingPayment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $order->refresh();

        return view('payments.success', compact('order'));
    }

    /**
     * YooKassa webhook handler.
     */
    public function webhook(Request $request)
    {
        $event = $request->all();

        Log::info('YooKassa webhook received', ['event' => $event['event'] ?? 'unknown']);

        try {
            $this->yooKassa->processWebhook($event);
        } catch (\Throwable $e) {
            Log::error('YooKassa webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $event,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
