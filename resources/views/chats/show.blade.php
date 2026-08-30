<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('chat.title') }}: {{ $chat->ad?->title }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="space-y-4">
                    @forelse($chat->messages as $message)
                        <div class="border rounded p-3">
                            <div class="text-sm text-gray-500 mb-1">{{ $message->user?->name }} · {{ $message->created_at->format('d.m.Y H:i') }}</div>
                            <div>{{ $message->message }}</div>
                        </div>
                    @empty
                        <div class="text-gray-600">{{ __('chat.no_messages') }}</div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('messages.store', $chat) }}" class="mt-6">
                    @csrf
                    <textarea name="message" rows="4" class="border rounded w-full px-3 py-2" placeholder="{{ __('chat.placeholder') }}"></textarea>
                    @error('message')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
                    <div class="mt-2 flex justify-end">
                        <button class="bg-amber-600 text-white px-4 py-2 rounded">{{ __('common.send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
