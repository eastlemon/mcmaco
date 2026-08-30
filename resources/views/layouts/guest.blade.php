<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'mcmaco'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-stone-50">
        <div class="min-h-screen grid lg:grid-cols-[5fr_7fr] 2xl:grid-cols-2">

            {{-- Брендовая панель --}}
            <div class="hidden lg:flex relative flex-col justify-between overflow-hidden bg-gradient-to-br from-amber-400 via-orange-500 to-orange-600 p-12 text-white">
                {{-- Декор --}}
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-32 -right-16 w-[28rem] h-[28rem] rounded-full border-[3rem] border-white/10"></div>
                <div class="absolute top-1/3 right-10 w-24 h-24 rounded-full bg-white/10"></div>

                <a href="/" class="relative">
                    <x-wordmark class="text-white" />
                </a>

                <div class="relative max-w-md">
                    <h1 class="text-4xl font-extrabold leading-tight">
                        Товары с доставкой по&nbsp;России
                    </h1>
                    <p class="mt-4 text-white/80 text-lg leading-relaxed">
                        Быстрый заказ в один клик, честные цены и возврат без вопросов.
                    </p>

                    <ul class="mt-10 space-y-5">
                        <li class="flex items-center gap-4">
                            <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <span class="font-medium">Быстрый заказ в 1 клик</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="font-medium">Доставка по всей стране</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            <span class="font-medium">Возврат в течение 14 дней</span>
                        </li>
                    </ul>
                </div>

                <p class="relative text-sm text-white/60">&copy; {{ date('Y') }} mcmaco</p>
            </div>

            {{-- Форма --}}
            <div class="flex flex-col items-center justify-center px-6 py-12 sm:px-12">
                <div class="w-full max-w-md">
                    <a href="/" class="lg:hidden inline-block mb-10">
                        <x-wordmark />
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>