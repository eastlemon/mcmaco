@props(['ad'])

@php
    $url = route('ads.show', $ad->slug);
    $imgUrl = $ad->cover_image?->url ?? asset('images/placeholder.svg');
    $inStock = $ad->stock > 0;
@endphp

<article
    class="group relative flex flex-col bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden {{ $inStock ? 'hover:-translate-y-0.5' : '' }}"
    x-data="{ added: false, loading: false }"
>
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
                Хит
            </span>
        @endif

        @if($ad->condition === 'used')
            <span class="absolute top-2.5 {{ $ad->is_featured ? 'left-16' : 'left-2.5' }} bg-gray-700/80 text-white text-[11px] font-medium px-2 py-0.5 rounded-full">
                Б/у
            </span>
        @endif

        @unless($inStock)
            <span class="absolute inset-x-0 bottom-0 bg-gray-900/70 text-white text-[11px] font-medium text-center py-1.5 backdrop-blur-sm">
                Нет в наличии
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
                    <span class="block text-[11px] font-medium text-orange-500">Осталось {{ $ad->stock }} шт.</span>
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
                <span x-show="!added && !loading">В корзину</span>
                <span x-show="loading" x-cloak class="inline-block animate-pulse">Добавляем…</span>
                <span x-show="added" x-cloak>✓ Добавлено</span>
            </button>
        @endif
    </div>
</article>