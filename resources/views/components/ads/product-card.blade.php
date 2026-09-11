@props(['ad'])

@php
    $url = route('ads.show', $ad->slug);
    $imgUrl = $ad->cover_image?->url ?? asset('images/placeholder.svg');
    $inStock = $ad->stock > 0;
    $isFavorite = $ad->isFavorited();
@endphp

<article
    class="group relative flex flex-col bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden {{ $inStock ? 'hover:-translate-y-0.5' : '' }}"
    x-data="{ added: false, loading: false, fav: {{ $isFavorite ? 'true' : 'false' }}, favLoading: false }"
>
    {{-- Кнопка избранного (toggle) --}}
    @if(auth()->check())
        <button type="button"
                :disabled="favLoading"
                @click="
                    favLoading = true;
                    const wasFav = fav;
                    fetch('{{ route('favorites.store', $ad) }}', {
                        method: wasFav ? 'DELETE' : 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                    .then(data => {
                        favLoading = false;
                        fav = data.favorite;
                        window.dispatchEvent(new CustomEvent('favorite-toggled', {
                            detail: { adId: {{ $ad->id }}, favorite: data.favorite }
                        }));
                    })
                    .catch(() => { favLoading = false; })
                "
                :title="fav ? @js(__('ads.in_favorites')) : @js(__('ads.add_to_favorites'))"
                :class="fav ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-white/90 text-gray-500 hover:text-amber-500 hover:bg-white'"
                class="absolute top-2.5 right-2.5 z-10 w-9 h-9 flex items-center justify-center rounded-full shadow-sm backdrop-blur-sm transition disabled:opacity-50">
            <svg x-show="fav" class="w-5 h-5 transition-transform" :class="favLoading ? 'scale-75' : 'scale-100'" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
            </svg>
            <svg x-show="!fav" x-cloak class="w-5 h-5 transition-transform" :class="favLoading ? 'scale-75' : 'scale-100'" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
            </svg>
        </button>
    @else
        <a href="{{ route('login') }}"
           title="{{ __('ads.add_to_favorites') }}"
           class="absolute top-2.5 right-2.5 z-10 w-9 h-9 flex items-center justify-center rounded-full bg-white/90 text-gray-500 shadow-sm backdrop-blur-sm transition hover:text-amber-500 hover:bg-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
            </svg>
        </a>
    @endif

    {{-- Обложка --}}
    <a href="{{ $url }}" class="relative block aspect-square overflow-hidden bg-stone-100" aria-label="{{ $ad->title }}">
        <img
            src="{{ $imgUrl }}"
            alt="{{ $ad->title }}"
            loading="lazy"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105 {{ $inStock ? '' : 'grayscale opacity-60' }}"
        >

        @if($ad->is_featured)
            <span class="absolute top-2.5 left-2.5 bg-amber-500 text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full shadow-sm">
                {{ __('ads.hit') }}
            </span>
        @endif

        @if($ad->condition === 'used')
            <span class="absolute top-2.5 {{ $ad->is_featured ? 'left-16' : 'left-2.5' }} bg-gray-700/80 text-white text-[11px] font-medium px-2 py-0.5 rounded-full">
                {{ __('ads.condition_used') }}
            </span>
        @endif

        @unless($inStock)
            <span class="absolute inset-x-0 bottom-0 bg-gray-900/70 text-white text-[11px] font-medium text-center py-1.5 backdrop-blur-sm">
                {{ __('ads.out_of_stock') }}
            </span>
        @endunless
    </a>

    {{-- Информация --}}
    <div class="flex flex-col flex-1 p-3.5">
        <a href="{{ $url }}" class="block">
            <h3 class="text-sm font-medium leading-snug text-gray-900 line-clamp-2 min-h-[2.6rem] group-hover:text-amber-600 transition">
                {{ $ad->title }}
            </h3>
        </a>

        <div class="flex items-center gap-2 text-xs text-gray-400 mt-1 mb-2.5">
            @if($ad->category)
                <span class="truncate">{{ $ad->category->name }}</span>
            @endif
            @if($ad->city)
                <span class="ml-auto shrink-0">{{ $ad->city }}</span>
            @endif
        </div>

        <div class="mt-auto flex items-end justify-between gap-2">
            <div class="leading-tight">
                <span class="block text-lg font-bold text-gray-900">{{ $ad->formatted_price }}</span>
                @if($inStock && $ad->stock <= 3)
                    <span class="block text-[11px] font-medium text-orange-500">{{ __('ads.stock_left', ['count' => $ad->stock]) }}</span>
                @endif
            </div>
        </div>

        @if($inStock)
            <button type="button"
                    @click="
                        loading = true;
                        fetch('{{ route('cart.add') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ ad_id: {{ $ad->id }}, qty: 1 })
                        })
                        .then(r => r.json())
                        .then(data => {
                            loading = false;
                            added = true;
                            setTimeout(() => added = false, 2000);
                            Livewire.dispatch('cart-updated', data);
                        })
                        .catch(() => { loading = false; })
                    "
                    :disabled="loading"
                    :class="added ? 'bg-green-600 text-white' : 'bg-amber-600 text-white hover:bg-amber-700 active:bg-amber-800'"
                    class="mt-2.5 w-full flex items-center justify-center gap-1.5 text-sm font-medium py-2 rounded-xl transition disabled:opacity-50">
                <svg x-show="!added && !loading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span x-show="!added && !loading">{{ __('shop.add_to_cart') }}</span>
                <span x-show="loading" x-cloak class="inline-block animate-pulse">{{ __('shop.adding') }}</span>
                <span x-show="added" x-cloak>✓ {{ __('shop.added') }}</span>
            </button>
        @endif
    </div>
</article>