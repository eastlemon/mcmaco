@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    <div class="bg-white rounded-lg shadow p-8 text-center">
        @if($order->status === \App\Models\Order::STATUS_PAID)
            <div class="text-green-500 text-6xl mb-4">✓</div>
            <h1 class="text-2xl font-bold mb-2">{{ __('shop.payment_success') }}</h1>
            <p class="text-gray-600 mb-6">{{ __('shop.order_paid', ['number' => $order->order_number]) }}</p>
        @else
            <div class="text-yellow-500 text-6xl mb-4">⏳</div>
            <h1 class="text-2xl font-bold mb-2">{{ __('shop.payment_processing') }}</h1>
            <p class="text-gray-600 mb-6">{{ __('shop.payment_pending', ['number' => $order->order_number]) }}</p>
        @endif

        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
            <div class="flex justify-between mb-2">
                <span class="text-gray-500">{{ __('shop.amount') }}:</span>
                <span class="font-semibold">{{ $order->formatted_total }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('shop.order_status') }}:</span>
                <span class="font-semibold">{{ $order->status_label }}</span>
            </div>
        </div>

        <a href="{{ route('ads.index') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
            {{ __('shop.back_to_shop') }}
        </a>
    </div>
</div>
@endsection
