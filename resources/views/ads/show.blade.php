@extends('layouts.app')

@section('meta_title', $ad->meta_title)
@section('meta_description', $ad->meta_description)
@section('og_type', 'product')

@push('head_extra')
    <script type="application/ld+json">
        {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-400 mb-4">
            <a href="{{ route('ads.index') }}" class="hover:text-amber-600">Главная</a>
            @if($ad->category)
                <span class="mx-1">/</span>
                <span>{{ $ad->category->name }}</span>
            @endif
            <span class="mx-1">/</span>
            <span class="text-gray-600">{{ $ad->title }}</span>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- Галерея --}}
            <div>
                @if($ad->images->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-3">
                        <img id="main-image" src="{{ asset('storage/' . $ad->images->first()->path) }}" alt="{{ $ad->title }}" class="w-full aspect-square object-cover">
                    </div>
                    @if($ad->images->count() > 1)
                        <div class="flex gap-2 overflow-x-auto">
                            @foreach($ad->images as $img)
                                <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $ad->title }}" loading="lazy"
                                     onclick="document.getElementById('main-image').src=this.src"
                                     class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-amber-400">
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="bg-white rounded-xl shadow-sm aspect-square flex items-center justify-center">
                        <span class="text-gray-300 text-6xl">📦</span>
                    </div>
                @endif
            </div>

            {{-- Инфо --}}
            <div>
                <h1 class="text-2xl font-bold mb-2">{{ $ad->title }}</h1>

                @if($ad->sku)
                    <div class="text-sm text-gray-400 mb-3">Артикул: {{ $ad->sku }}</div>
                @endif

                <div class="text-3xl font-bold text-amber-700 mb-4">{{ $ad->formatted_price }}</div>

                <div class="flex items-center gap-3 mb-6">
                    @if($ad->stock > 0)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">✓ В наличии ({{ $ad->stock }} шт.)</span>
                    @else
                        <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-full text-sm">Нет в наличии</span>
                    @endif
                    <span class="text-sm text-gray-400">{{ $ad->condition === 'new' ? 'Новое' : 'Б/у' }}</span>
                </div>

                <div class="prose prose-sm max-w-none mb-6">
                    {!! nl2br(e($ad->description)) !!}
                </div>

                {{-- Действия --}}
                <div class="space-y-3">
                    @if($ad->stock > 0)
                        <livewire:add-to-cart :ad-id="$ad->id" :key="'cart-' . $ad->id" />
                        <livewire:quick-order :ad-id="$ad->id" :key="'quick-' . $ad->id" />
                    @endif

                    <div class="flex gap-2 pt-2">
                        @auth
                            <form method="POST" action="{{ route('chats.store', $ad) }}">
                                @csrf
                                <button class="flex-1 border px-4 py-2 rounded-lg text-sm hover:bg-gray-50">💬 Написать продавцу</button>
                            </form>

                            @php
                                $isFavorite = auth()->user()?->favorites()->where('ad_id', $ad->id)->exists();
                            @endphp
                            <form method="POST" action="{{ route($isFavorite ? 'favorites.destroy' : 'favorites.store', $ad) }}">
                                @csrf
                                @if($isFavorite) @method('DELETE') @endif
                                <button class="border px-4 py-2 rounded-lg text-sm hover:bg-gray-50">
                                    {{ $isFavorite ? '★ В избранном' : '☆ В избранное' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="flex-1 text-center border px-4 py-2 rounded-lg text-sm hover:bg-gray-50">Войти</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        {{-- Похожие --}}
        @if($related->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-xl font-bold mb-4">Похожие товары</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($related as $item)
                        @include('ads.partials.card', ['ad' => $item])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection