@extends('layouts.app')

@section('meta_title', $ad->meta_title)
@section('meta_description', $ad->meta_description)
@section('og_type', 'product')
@section('og_title', $ad->title . ' — ' . config('app.name'))

@push('head_extra')
    @php
        $imageUrl = $ad->images->isNotEmpty() ? asset('storage/' . $ad->images->first()->path) : null;
    @endphp
    @if($imageUrl)
        <meta property="og:image" content="{{ $imageUrl }}">
        <meta name="twitter:image" content="{{ $imageUrl }}">
    @endif

    <script type="application/ld+json">
        {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    @php
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => route('ads.index')],
        ];
        if ($ad->category) {
            $breadcrumbs[] = ['name' => $ad->category->name, 'url' => route('ads.index') . '?category_id=' . $ad->category->id];
        }
        $breadcrumbs[] = ['name' => $ad->title, 'url' => route('ads.show', $ad->slug)];
    @endphp
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)->map(fn ($crumb, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ])->values()->toArray(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-400 mb-4">
            <a href="{{ route('ads.index') }}" class="hover:text-amber-600">Главная</a>
            @if($ad->category)
                <span class="mx-1">/</span>
                <a href="{{ route('ads.index', ['category_id' => $ad->category->id]) }}" class="hover:text-amber-600">{{ $ad->category->name }}</a>
            @endif
            <span class="mx-1">/</span>
            <span class="text-gray-600">{{ $ad->title }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Галерея --}}
            <div>
                @if($ad->images->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-3">
                        <img id="main-image" src="{{ asset('storage/' . $ad->images->first()->path) }}" alt="{{ $ad->title }}" class="w-full aspect-square object-cover">
                    </div>
                    @if($ad->images->count() > 1)
                        <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                            @foreach($ad->images as $img)
                                <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $ad->title }}" loading="lazy"
                                     onclick="document.getElementById('main-image').src=this.src"
                                     class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-amber-400 transition shrink-0">
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
            <div class="flex flex-col gap-4">

                {{-- Заголовок + цена --}}
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h1 class="text-xl font-bold text-gray-800 mb-2">{{ $ad->title }}</h1>

                    <div class="flex items-center gap-3 mb-3">
                        @if($ad->stock > 0)
                            <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">✓ В наличии</span>
                        @else
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-500 rounded-full text-xs">Нет в наличии</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $ad->condition === 'new' ? 'Новое' : 'Б/у' }}</span>
                        @if($ad->sku)
                            <span class="text-xs text-gray-400 ml-auto">Артикул: {{ $ad->sku }}</span>
                        @endif
                    </div>

                    <div class="text-3xl font-bold text-amber-700 mb-4">{{ $ad->formatted_price }}</div>

                    {{-- Кнопки --}}
                    @if($ad->stock > 0)
                        <div class="space-y-2">
                            <livewire:add-to-cart :ad-id="$ad->id" :key="'cart-' . $ad->id" />
                            <livewire:quick-order :ad-id="$ad->id" :key="'quick-' . $ad->id" />
                        </div>
                    @endif

                    {{-- Доп. действия --}}
                    <div class="flex gap-2 pt-3 mt-3 border-t">
                        @auth
                            <form method="POST" action="{{ route('chats.store', $ad) }}">
                                @csrf
                                <button class="flex-1 border px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">💬 Написать продавцу</button>
                            </form>

                            @php
                                $isFavorite = auth()->user()?->favorites()->where('ad_id', $ad->id)->exists();
                            @endphp
                            <form method="POST" action="{{ route($isFavorite ? 'favorites.destroy' : 'favorites.store', $ad) }}">
                                @csrf
                                @if($isFavorite) @method('DELETE') @endif
                                <button class="border px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                                    {{ $isFavorite ? '★ В избранном' : '☆ В избранное' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="flex-1 text-center border px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">Войти</a>
                        @endauth
                    </div>
                </div>

                {{-- Доставка --}}
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-3 text-sm">🚚 Доставка</h3>
                    <div class="space-y-2 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            <span>Самовывоз — бесплатно</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>
                            <span>СДЭК — от 350 ₽</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>Почта России — от 250 ₽</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Курьер — от 400 ₽</span>
                        </div>
                    </div>
                </div>

                {{-- Оплата --}}
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-3 text-sm">💳 Оплата</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1.5 rounded-full">Онлайн (ЮKassa)</span>
                        <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1.5 rounded-full">При получении</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Описание --}}
        @if($ad->description)
        <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
            <h2 class="font-semibold text-lg text-gray-800 mb-3">Описание</h2>
            <div class="prose prose-sm max-w-none text-gray-600">
                {!! nl2br(e($ad->description)) !!}
            </div>
        </div>
        @endif

        {{-- Похожие --}}
        @if($related->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Похожие товары</h2>
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