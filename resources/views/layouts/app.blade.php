<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('meta_title', config('app.name'))</title>
        <meta name="description" content="@yield('meta_description', 'mcma.co — интернет-магазин товаров с доставкой')">

        <!-- Open Graph -->
        <meta property="og:title" content="@yield('og_title', config('app.name'))">
        <meta property="og:description" content="@yield('meta_description', 'mcma.co — интернет-магазин')">
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:url" content="{{ request()->url() }}">
        @yield('og_image')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @yield('head_extra')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>

            <footer class="bg-white border-t mt-12 py-8">
                <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-400">
                    © {{ date('Y') }} mcma.co
                </div>
            </footer>
        </div>
    </body>
</html>