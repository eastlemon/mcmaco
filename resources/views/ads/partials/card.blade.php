@php
    $cover = $ad->coverImage;
    $imgUrl = $cover ? asset('storage/' . $cover->path) : 'https://placehold.co/400x300?text=mcmaco';
@endphp

<div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group flex flex-col"
     x-data="{ added: false, loading: false }">
    <a href="{{ route('ads.show', $ad->slug) }}" class="block">
        <div class="aspect-square overflow-hidden bg-gray-100 relative">
            <img src="{{ $imgUrl }}" alt="{{ $ad->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition">
            @if($ad->is_featured)
                <span class="absolute top-2 left-2 bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">⭐ Хит</span>
            @endif
        </div>
    </a>
    <div class="p-3 flex-1 flex flex-col">
        <a href="{{ route('ads.show', $ad->slug) }}" class="block">
            <h3 class="font-medium text-sm text-gray-800 line-clamp-2 mb-1 hover:text-amber-600 transition">{{ $ad->title }}</h3>
        </a>
        @if($ad->category)
            <div class="text-xs text-gray-400 mb-2">{{ $ad->category->name }}</div>
        @endif
        <div class="flex items-center justify-between mt-auto mb-2">
            <span class="text-lg font-bold text-amber-700">{{ $ad->formatted_price }}</span>
            @if($ad->stock > 0)
                <span class="text-xs text-green-600">в наличии</span>
            @else
                <span class="text-xs text-gray-400">нет в наличии</span>
            @endif
        </div>
        @if($ad->stock > 0)
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
                    :class="added ? 'bg-green-600 text-white' : 'bg-amber-600 text-white hover:bg-amber-700'"
                    class="w-full text-sm py-2 rounded-lg transition font-medium disabled:opacity-50">
                <span x-show="!added && !loading">В корзину</span>
                <span x-show="loading" x-cloak>Добавляем...</span>
                <span x-show="added" x-cloak>✓ Добавлено</span>
            </button>
        @endif
    </div>
</div>