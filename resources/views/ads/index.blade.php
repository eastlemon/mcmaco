@extends('layouts.app')

@section('meta_title', 'mcma.co — товары с доставкой')
@section('meta_description', 'Интернет-магазин mcma.co: популярные товары и новинки с доставкой по России')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Hero --}}
        @if(!request()->hasAny(['q', 'category_id', 'city', 'condition', 'min_price', 'max_price', 'page']))
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl p-8 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">mcma.co</h1>
                <p class="text-lg opacity-90">Товары с доставкой по России</p>
            </div>

            {{-- Категории --}}
            @if($categories->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-lg font-semibold mb-4 text-gray-700">Категории</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($categories as $category)
                            <a href="?category_id={{ $category->id }}" class="px-4 py-2 bg-white shadow-sm rounded-full text-sm hover:bg-amber-50 transition">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Популярные --}}
            @php
                $featured = \App\Models\Ad::featured()->with(['images', 'category'])->limit(8)->get();
            @endphp
            @if($featured->isNotEmpty())
                <div class="mb-10">
                    <h2 class="text-xl font-bold mb-4">⭐ Популярные товары</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($featured as $item)
                            @include('ads.partials.card', ['ad' => $item])
                        @endforeach
                    </div>
                </div>
            @endif

            <h2 class="text-xl font-bold mb-4">🆕 Все товары</h2>
        @else
            <h1 class="text-2xl font-bold mb-6">Товары</h1>
        @endif

        {{-- Фильтры --}}
        <form method="GET" class="bg-white shadow rounded-lg p-4 mb-6 grid grid-cols-1 md:grid-cols-6 gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Поиск" class="border rounded px-3 py-2 md:col-span-2">

            <select name="category_id" class="border rounded px-3 py-2">
                <option value="">Категория</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="condition" class="border rounded px-3 py-2">
                <option value="">Состояние</option>
                <option value="new" @selected(request('condition') === 'new')>Новое</option>
                <option value="used" @selected(request('condition') === 'used')>Б/у</option>
            </select>

            <select name="sort" class="border rounded px-3 py-2">
                <option value="newest" @selected(request('sort') === 'newest' || !request('sort'))>Сначала новые</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Цена ↑</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Цена ↓</option>
            </select>

            <button class="bg-amber-600 text-white px-4 py-2 rounded">Применить</button>

            @if(request('min_price') || request('max_price'))
                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="от" class="border rounded px-3 py-2">
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="до" class="border rounded px-3 py-2">
            @endif
        </form>

        {{-- Список --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($ads as $ad)
                @include('ads.partials.card', ['ad' => $ad])
            @empty
                <div class="col-span-full text-center text-gray-500 py-12">Товары не найдены</div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $ads->links() }}
        </div>
    </div>
</div>
@endsection