<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('ads.favorites') }}</h2>
    </x-slot>

    <div class="py-6" x-data="{ count: {{ $favorites->count() }} }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div x-show="count > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($favorites as $favorite)
                    <div x-data="{ hidden: false }"
                         x-show="!hidden"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         @favorite-toggled.window="if ($event.detail.adId === {{ $favorite->ad->id }} && !$event.detail.favorite) { hidden = true; count--; }">
                        <x-ads.product-card :ad="$favorite->ad" />
                    </div>
                @endforeach
            </div>

            <div x-cloak x-show="count === 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                </svg>
                <p class="text-gray-500">{{ __('favorites.empty') }}</p>
            </div>

            <div x-show="count > 0" class="mt-6">
                {{ $favorites->links() }}
            </div>
        </div>
    </div>
</x-app-layout>