@extends('layouts.app')

@section('meta_title', 'Страница не найдена — mcmaco')

@section('content')
<div class="py-20">
    <div class="max-w-md mx-auto px-4 text-center">
        <div class="text-7xl font-bold text-amber-600 mb-4">404</div>
        <h1 class="text-xl font-semibold text-gray-700 mb-2">Страница не найдена</h1>
        <p class="text-gray-400 text-sm mb-6">
            К сожалению, такой страницы не существует. Возможно, она была удалена или вы перешли по неверной ссылке.
        </p>
        <a href="{{ route('ads.index') }}" class="inline-block bg-amber-600 text-white px-6 py-3 rounded-lg hover:bg-amber-700 transition font-medium">
            ← В каталог
        </a>
    </div>
</div>
@endsection