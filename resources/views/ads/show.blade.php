@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('ads.index') }}" class="text-amber-700 hover:underline">← Назад к объявлениям</a>

        <div class="bg-white shadow rounded-lg p-6 mt-4">
            <h1 class="text-2xl font-bold mb-2">{{ $ad->title }}</h1>
            <div class="text-gray-500 mb-4">{{ $ad->city }} · {{ $ad->category?->name }}</div>

            <div class="text-2xl font-semibold mb-4">{{ number_format($ad->price, 0, ',', ' ') }} ₽</div>

            <div class="prose max-w-none">
                {{ $ad->description }}
            </div>

            <div class="mt-6 flex items-center gap-3">
                @auth
                    <form method="POST" action="{{ route('chats.store', $ad) }}">
                        @csrf
                        <button class="bg-amber-600 text-white px-4 py-2 rounded">Написать продавцу</button>
                    </form>

                    @php
                        $isFavorite = auth()->user()
                            ?->favorites()
                            ->where('ad_id', $ad->id)
                            ->exists();
                    @endphp

                    @if($isFavorite)
                        <form method="POST" action="{{ route('favorites.destroy', $ad) }}">
                            @csrf
                            @method('DELETE')
                            <button class="border px-4 py-2 rounded">Убрать из избранного</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('favorites.store', $ad) }}">
                            @csrf
                            <button class="border px-4 py-2 rounded">В избранное</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="bg-amber-600 text-white px-4 py-2 rounded inline-block">Войти, чтобы написать</a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
