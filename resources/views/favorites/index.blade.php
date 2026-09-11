<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('ads.favorites') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($favorites->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($favorites as $favorite)
                        <div class="relative group/fav">
                            <x-ads.product-card :ad="$favorite->ad" />

                            {{-- Убрать из избранного --}}
                            <form method="POST" action="{{ route('favorites.destroy', $favorite->ad) }}" class="absolute top-2 right-2 z-10">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="{{ __('ads.in_favorites') }}"
                                        class="w-9 h-9 flex items-center justify-center bg-white/90 backdrop-blur-sm text-amber-500 rounded-full shadow-sm hover:bg-red-50 hover:text-red-500 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $favorites->links() }}
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                    </svg>
                    <p class="text-gray-500">{{ __('favorites.empty') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
