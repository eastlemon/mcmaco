<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('ads.favorites') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg divide-y">
                @forelse($favorites as $favorite)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <div class="font-semibold">
                                <a href="{{ route('ads.show', $favorite->ad) }}" class="text-amber-700 hover:underline">
                                    {{ $favorite->ad?->title }}
                                </a>
                            </div>
                            <div class="text-sm text-gray-500">{{ $favorite->ad?->city }} · {{ $favorite->ad?->category?->name }}</div>
                        </div>
                        <form method="POST" action="{{ route('favorites.destroy', $favorite->ad) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 text-sm">{{ __('common.remove') }}</button>
                        </form>
                    </div>
                @empty
                    <div class="p-4 text-gray-600">{{ __('favorites.empty') }}</div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $favorites->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
