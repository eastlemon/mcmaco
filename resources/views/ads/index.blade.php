@extends('layouts.app')

@section('meta_title', 'mcma.co — товары с доставкой')
@section('meta_description', 'Интернет-магазин mcma.co: популярные товары и новинки с доставкой по России')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Hero (only on clean landing, no query params) --}}
        @if(!request()->hasAny(['q', 'category_id', 'city', 'condition', 'min_price', 'max_price', 'sort', 'page', 'inStockOnly', 'featuredOnly']))
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl p-8 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">mcma.co</h1>
                <p class="text-lg opacity-90">Товары с доставкой по России</p>
            </div>

            {{-- Категории --}}
            @php
                $categories = \App\Models\Category::roots()->get();
            @endphp
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
        @endif

        {{-- Livewire Product Browser --}}
        <livewire:product-browser />

    </div>
</div>
@endsection
