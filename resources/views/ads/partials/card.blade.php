@php
    $cover = $ad->coverImage;
    $imgUrl = $cover ? asset('storage/' . $cover->path) : 'https://placehold.co/400x300?text=mcmaco';
@endphp

<div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group flex flex-col">
    <a href="{{ route('ads.show', $ad->slug) }}" class="block">
        <div class="aspect-square overflow-hidden bg-gray-100">
            <img src="{{ $imgUrl }}" alt="{{ $ad->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition">
        </div>
    </a>
    <div class="p-3 flex-1 flex flex-col">
        <a href="{{ route('ads.show', $ad->slug) }}" class="block">
            <h3 class="font-medium text-sm text-gray-800 line-clamp-2 mb-1 hover:text-amber-600 transition">{{ $ad->title }}</h3>
        </a>
        @if($ad->category)
            <div class="text-xs text-gray-400 mb-2">{{ $ad->category->name }}</div>
        @endif
        <div class="flex items-center justify-between mt-auto">
            <span class="text-lg font-bold text-amber-700">{{ $ad->formatted_price }}</span>
            @if($ad->stock > 0)
                <span class="text-xs text-green-600">в наличии</span>
            @else
                <span class="text-xs text-gray-400">нет в наличии</span>
            @endif
        </div>
        @if($ad->stock > 0)
            <form action="{{ route('cart.add') }}" method="POST" class="mt-2">
                @csrf
                <input type="hidden" name="ad_id" value="{{ $ad->id }}">
                <button type="submit"
                        class="w-full bg-amber-600 text-white text-sm py-2 rounded-lg hover:bg-amber-700 transition font-medium">
                    В корзину
                </button>
            </form>
        @endif
    </div>
</div>