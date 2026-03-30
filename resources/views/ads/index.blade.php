@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold mb-6">Объявления</h1>

        <form method="GET" class="bg-white shadow rounded-lg p-4 mb-6 grid grid-cols-1 md:grid-cols-6 gap-4">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Поиск" class="border rounded px-3 py-2 md:col-span-2">

            <select name="category_id" class="border rounded px-3 py-2">
                <option value="">Категория</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <input type="text" name="city" value="{{ request('city') }}" placeholder="Город" class="border rounded px-3 py-2">

            <select name="condition" class="border rounded px-3 py-2">
                <option value="">Состояние</option>
                <option value="new" @selected(request('condition') === 'new')>Новое</option>
                <option value="used" @selected(request('condition') === 'used')>Б/у</option>
            </select>

            <select name="sort" class="border rounded px-3 py-2">
                <option value="newest" @selected(request('sort') === 'newest' || request('sort') === null)>Сначала новые</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Цена ↑</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Цена ↓</option>
            </select>

            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Цена от" class="border rounded px-3 py-2">
            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Цена до" class="border rounded px-3 py-2">

            <div class="md:col-span-6 flex gap-2">
                <button class="bg-amber-600 text-white px-4 py-2 rounded">Применить</button>
                <a href="{{ route('ads.index') }}" class="px-4 py-2 rounded border">Сбросить</a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($ads as $ad)
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="font-semibold text-lg mb-2">
                        <a href="{{ route('ads.show', $ad) }}" class="text-amber-700 hover:underline">{{ $ad->title }}</a>
                    </div>
                    <div class="text-sm text-gray-500 mb-2">{{ $ad->city }} · {{ $ad->category?->name }}</div>
                    <div class="text-xl font-bold mb-3">{{ number_format($ad->price, 0, ',', ' ') }} ₽</div>
                    <div class="text-sm text-gray-600 line-clamp-3">{{ $ad->description }}</div>
                </div>
            @empty
                <div class="text-gray-600">Нет объявлений по заданным фильтрам.</div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $ads->links() }}
        </div>
    </div>
</div>
@endsection
