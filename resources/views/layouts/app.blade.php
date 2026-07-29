<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('meta_title', config('app.name') . ' — интернет-магазин товаров с доставкой')</title>
        <meta name="description" content="@yield('meta_description', 'mcmaco — интернет-магазин товаров с доставкой по России')">
        <meta name="robots" content="index, follow">

        <!-- Open Graph -->
        <meta property="og:site_name" content="mcmaco">
        <meta property="og:locale" content="ru_RU">
        <meta property="og:title" content="@yield('og_title', config('app.name'))">
        <meta property="og:description" content="@yield('meta_description', 'mcmaco — интернет-магазин товаров с доставкой по России')">
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:url" content="{{ request()->url() }}">
        @yield('og_image')

        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="@yield('og_title', config('app.name'))">
        <meta name="twitter:description" content="@yield('meta_description', 'mcmaco — интернет-магазин товаров с доставкой по России')">
        @yield('twitter_image')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Canonical -->
        <link rel="canonical" href="{{ config('app.url') . request()->getPathInfo() }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head_extra')
        @stack('analytics')
    </head>
    <body class="font-sans antialiased flex flex-col min-h-screen bg-gray-100">
        <div class="flex flex-col flex-1 min-h-screen">
            @include('layouts.navigation')

            @includeWhen(config('analytics.ga4.enabled'), 'analytics.ga4')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1 flex flex-col">
                @yield('content')
            </main>

            @include('layouts.footer')
        </div>

        @includeWhen(config('analytics.metrika.enabled'), 'analytics.metrika')
    </body>
</html>