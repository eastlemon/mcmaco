@extends('layouts.app')

@section('meta_title', $category->meta_title)
@section('meta_description', $category->meta_description)

@push('head_extra')
    <script type="application/ld+json">
        {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs --}}
        <nav class="text-sm text-gray-400 mb-4">
            <a href="{{ route('ads.index') }}" class="hover:text-amber-600">{{ __('common.home') }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-600">{{ $category->name }}</span>
        </nav>

        {{-- Title + Sort --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $category->name }}</h1>
            </div>
            <form action="{{ route('categories.show', $category->slug) }}" method="GET" class="flex items-center gap-2">
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                <select name="sort" onchange="this.form.submit()"
                        class="border rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>{{ __('ads.sort.newest') }}</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('ads.sort.price_asc') }}</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('ads.sort.price_desc') }}</option>
                    <option value="popular" @selected(request('sort') === 'popular')>{{ __('ads.sort.popular') }}</option>
                </select>
            </form>
        </div>

        {{-- Subcategories --}}
        @if($category->children->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-5">
                @foreach($category->children as $child)
                    @php $count = $child->ads()->active()->count(); @endphp
                    <a href="{{ route('ads.index', ['category_id' => $child->id]) }}"
                       class="text-sm px-3 py-1.5 bg-white shadow-sm rounded-full text-gray-600 hover:bg-amber-50 hover:text-amber-600 transition">
                        {{ $child->name }} @if($count) <span class="text-xs text-gray-400">{{ $count }}</span> @endif
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Search within category --}}
        <form action="{{ route('categories.show', $category->slug) }}" method="GET" class="relative max-w-md mb-4">
            @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('common.search_placeholder') }}"
                   class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm bg-gray-50 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </form>

        {{-- Count --}}
        <div class="text-sm text-gray-500 mb-4">
            {{ __('ads.found') }}: <span class="font-medium text-gray-700">{{ $ads->total() }}</span> {{ __('ads.items_count') }}
        </div>

        {{-- Grid --}}
        @if($ads->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                @foreach($ads as $ad)
                    <x-ads.product-card :ad="$ad" />
                @endforeach
            </div>

            <div class="mt-6">
                {{ $ads->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <div class="text-5xl mb-4">🔍</div>
                <h3 class="text-lg font-medium text-gray-600 mb-2">{{ __('ads.category_empty') }}</h3>
                <a href="{{ route('ads.index') }}" class="text-amber-600 hover:text-amber-700 text-sm font-medium">{{ __('ads.go_to_catalog') }}</a>
            </div>
        @endif
    </div>
</div>
@endsection