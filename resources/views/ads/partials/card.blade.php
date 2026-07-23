@php
    $cover = $ad->coverImage;
    $imgUrl = $cover ? asset('storage/' . $cover->path) : 'https://placehold.co/400x300?text=mcma.co';
@endphp

<a href="{{ route('ads.show', $ad->slug) }}" class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">
    <div class="aspect-square overflow-hidden bg-gray-100">
        <img src="{{ $imgUrl }}" alt="{{ $ad->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition">
    </div>
    <div class="p-3">
        <h3 class="font-medium text-sm text-gray-800 line-clamp-2 mb-1">{{ $ad->title }}</h3>
        <div class="flex items-center justify-between">
            <span class="text-lg font-bold text-amber-700">{{ $ad->formatted_price }}</span>
            @if($ad->stock > 0)
                <span class="text-xs text-green-600">в наличии</span>
            @else
                <span class="text-xs text-gray-400">нет в наличии</span>
            @endif
        </div>
        @if($ad->category)
            <div class="text-xs text-gray-400 mt-1">{{ $ad->category->name }}</div>
        @endif
    </div>
</a>