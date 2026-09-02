@extends('layouts.app')

@include('schema.home')

@section('meta_title', __('ads.meta_title'))
@section('meta_description', __('ads.meta_description'))

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs - Only show when not on main page --}}
        @if(request()->hasAny(['q', 'category_id', 'city', 'condition', 'min_price', 'max_price', 'sort', 'page', 'inStockOnly', 'featuredOnly']))
            <nav class="text-sm text-gray-400 mb-4">
                @if(request('category_id'))
                    @php $cat = \App\Models\Category::find(request('category_id')); @endphp
                    @if($cat)
                        <a href="{{ route('ads.index') }}" class="hover:text-amber-600">{{ __('common.home') }}</a>
                        <span class="mx-1">/</span>
                        <a href="{{ $cat->slug ? route('categories.show', $cat->slug) : route('ads.index', ['category_id' => $cat->id]) }}" class="hover:text-amber-600">{{ $cat->name }}</a>
                    @endif
                @else
                    <span class="text-gray-600">{{ __('common.home') }}</span>
                @endif
            </nav>
        @endif

        {{-- Hero (only on clean landing) --}}
        @if(!request()->hasAny(['q', 'category_id', 'city', 'condition', 'min_price', 'max_price', 'sort', 'page', 'inStockOnly', 'featuredOnly']))
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl p-6 mb-6 text-white">
                <h1 class="text-2xl font-bold mb-1">mcmaco</h1>
                <p class="text-base opacity-90">{{ __('ads.hero_subtitle') }}</p>
            </div>

            {{-- Popular Products --}}
            @php
                $featured = \App\Models\Ad::featured()->with(['images', 'category'])->limit(8)->get();
            @endphp
            @if($featured->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-lg font-bold mb-4 text-gray-800">⭐ {{ __('ads.popular') }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($featured as $item)
                            <x-ads.product-card :ad="$item" />
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- "All products" is now hidden on main page --}}
            {{-- <h2 class="text-lg font-bold mb-4 text-gray-800">🛒 {{ __('ads.all_items') }}</h2> --}}
        @endif

        {{-- Livewire Product Browser --}}
        <livewire:product-browser />

    </div>
</div>
@endsection
