<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Мои объявления</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('ads.manage.create') }}" class="bg-amber-600 text-white px-4 py-2 rounded">Создать объявление</a>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="divide-y">
                    @forelse($ads as $ad)
                        <div class="p-4 flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $ad->title }}</div>
                                <div class="text-sm text-gray-500">{{ $ad->city }} · {{ $ad->status }}</div>
                            </div>
                            <a class="text-amber-700 hover:underline" href="{{ route('ads.manage.edit', $ad) }}">Редактировать</a>
                        </div>
                    @empty
                        <div class="p-4 text-gray-600">Пока нет объявлений.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4">
                {{ $ads->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
