@php
    $cats = \App\Models\Category::roots()->withCount('ads')->get();
@endphp

@if($cats->isNotEmpty())
<div class="bg-white border-b border-gray-100 sticky top-16 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-1 overflow-x-auto py-2.5 scrollbar-hide">
            <a href="{{ route('ads.index') }}"
               class="shrink-0 px-3 py-1.5 text-sm rounded-full transition {{ request()->routeIs('ads.index') && !request('category_id') ? 'bg-amber-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                Все товары
            </a>
            @foreach($cats as $cat)
                <a href="{{ route('ads.index', ['category_id' => $cat->id]) }}"
                   class="shrink-0 px-3 py-1.5 text-sm rounded-full transition {{ request('category_id') == $cat->id ? 'bg-amber-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    {{ $cat->name }}
                    @if($cat->ads_count > 0)
                        <span class="text-xs opacity-60">{{ $cat->ads_count }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif