<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('chat.my_chats') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg divide-y">
                @forelse($chats as $chat)
                    <a href="{{ route('chats.show', $chat) }}" class="block p-4 hover:bg-gray-50">
                        <div class="font-semibold">{{ $chat->ad?->title }}</div>
                        <div class="text-sm text-gray-500">{{ __('chat.buyer') }}: {{ $chat->buyer?->name }} · {{ __('chat.seller') }}: {{ $chat->seller?->name }}</div>
                    </a>
                @empty
                    <div class="p-4 text-gray-600">{{ __('chat.empty') }}</div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $chats->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
