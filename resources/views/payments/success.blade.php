@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    <div class="bg-white rounded-lg shadow p-8 text-center">
        @if($order->status === \App\Models\Order::STATUS_PAID)
            <div class="text-green-500 text-6xl mb-4">✓</div>
            <h1 class="text-2xl font-bold mb-2">Оплата получена!</h1>
            <p class="text-gray-600 mb-6">Заказ {{ $order->order_number }} успешно оплачен.</p>
        @else
            <div class="text-yellow-500 text-6xl mb-4">⏳</div>
            <h1 class="text-2xl font-bold mb-2">Платёж обрабатывается</h1>
            <p class="text-gray-600 mb-6">Заказ {{ $order->order_number }}. Статус обновится автоматически.</p>
        @endif

        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
            <div class="flex justify-between mb-2">
                <span class="text-gray-500">Сумма:</span>
                <span class="font-semibold">{{ $order->formatted_total }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Статус:</span>
                <span class="font-semibold">{{ $order->status_label }}</span>
            </div>
        </div>

        <a href="{{ route('ads.index') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
            Вернуться в магазин
        </a>
    </div>
</div>
@endsection
