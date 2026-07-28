@extends('layouts.app')

@section('meta_title', 'Корзина — mcmaco')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-400 mb-4">
            <a href="{{ route('ads.index') }}" class="hover:text-amber-600">Главная</a>
            <span class="mx-1">/</span>
            <span class="text-gray-600">Корзина</span>
        </nav>

        <h1 class="text-2xl font-bold mb-6 text-gray-800">Корзина</h1>

        @if($items->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <div class="text-6xl mb-4">🛒</div>
                <p class="text-gray-400 mb-4">Корзина пуста</p>
                <a href="{{ route('ads.index') }}" class="inline-block bg-amber-600 text-white px-6 py-2 rounded-lg hover:bg-amber-700 transition">Каталог товаров</a>
            </div>
        @else
            <div class="space-y-3 mb-6">
                @foreach($items as $item)
                    @php $img = $item->ad->coverImage ? asset('storage/' . $item->ad->coverImage->path) : 'https://placehold.co/80x80?text=mcmaco'; @endphp
                    <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-4">
                        <a href="{{ route('ads.show', $item->ad->slug) }}">
                            <img src="{{ $img }}" alt="{{ $item->ad->title }}" class="w-20 h-20 object-cover rounded-lg">
                        </a>

                        <div class="flex-1">
                            <a href="{{ route('ads.show', $item->ad->slug) }}" class="font-medium hover:text-amber-600 transition">{{ $item->ad->title }}</a>
                            <div class="text-sm text-gray-400">{{ number_format($item->ad->price, 0, ',', ' ') }} ₽ / шт.</div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button wire:click="decrementQty({{ $item->ad_id }})" class="w-8 h-8 rounded border hover:bg-gray-50 transition">−</button>
                            <span class="w-8 text-center">{{ $item->qty }}</span>
                            <button wire:click="incrementQty({{ $item->ad_id }})" class="w-8 h-8 rounded border hover:bg-gray-50 transition">+</button>
                        </div>

                        <div class="text-right w-28">
                            <div class="font-bold text-amber-700">{{ number_format($item->subtotal, 0, ',', ' ') }} ₽</div>
                        </div>

                        <button wire:click="removeItem({{ $item->ad_id }})" wire:confirm="Удалить товар?" class="text-gray-300 hover:text-red-500 transition">
                            ✕
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-400">Итого</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($total, 0, ',', ' ') }} ₽</div>
                </div>
                <a href="{{ route('checkout.index') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-medium px-8 py-3 rounded-lg transition">
                    Оформить заказ →
                </a>
            </div>
        @endif
    </div>
</div>
@endsection