@extends('layouts.app')

@section('meta_title', 'Заказ ' . $order->order_number . ' — mcmaco')

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <div class="text-5xl mb-4">✅</div>
            <h1 class="text-2xl font-bold mb-2">Заказ оформлен!</h1>
            <div class="text-gray-500 mb-1">Номер заказа: <span class="font-mono font-medium text-gray-800">{{ $order->order_number }}</span></div>
            <div class="text-sm text-gray-400 mb-6">Статус: {{ $order->status_label }}</div>

            <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
                <div class="text-sm font-medium mb-2">Состав заказа</div>
                @foreach($order->items as $item)
                    <div class="flex justify-between text-sm py-1">
                        <span>{{ $item->title_snapshot }} ×{{ $item->qty }}</span>
                        <span>{{ $item->formatted_subtotal }}</span>
                    </div>
                @endforeach
                <div class="border-t mt-2 pt-2 flex justify-between font-bold">
                    <span>Итого</span>
                    <span>{{ $order->formatted_total }}</span>
                </div>
            </div>

            <div class="text-sm text-gray-500 mb-6">
                Доставка: {{ $order->delivery_method_label }}<br>
                @if($order->delivery_address) {{ $order->delivery_address }} @endif
            </div>

            @if(in_array($order->status, [\App\Models\Order::STATUS_NEW, \App\Models\Order::STATUS_CONFIRMED]) && app(\App\Services\YooKassaService::class)->isEnabled())
                <div class="flex flex-col gap-3 items-center mb-6">
                    <a href="{{ route('payments.pay', $order) }}" class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition font-medium">
                        💳 Оплатить онлайн
                    </a>
                    <span class="text-xs text-gray-400">или оплатите при получении</span>
                </div>
            @else
                <p class="text-sm text-gray-400 mb-6">Мы свяжемся с вами по телефону для подтверждения заказа.</p>
            @endif

            @if(session('error'))
                <div class="bg-red-50 text-red-600 text-sm rounded-lg p-3 mb-4">{{ session('error') }}</div>
            @endif

            <a href="{{ route('ads.index') }}" class="inline-block bg-amber-600 text-white px-6 py-2 rounded-lg hover:bg-amber-700">
                Продолжить покупки
            </a>
        </div>
    </div>
</div>
@endsection