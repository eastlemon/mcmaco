<x-guest-layout>
    @section('title', 'Подтверждение email — mcmaco')
    <h1 class="text-3xl font-extrabold tracking-tight">Подтвердите email</h1>
    <p class="mt-1.5 text-sm text-gray-400">Мы отправили ссылку для подтверждения на ваш адрес</p>

    <div class="mt-8">
        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 flex items-start gap-3 rounded-xl bg-green-50 border border-green-100 p-4 text-sm text-green-700">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Новая ссылка отправлена на указанный при регистрации адрес.</span>
            </div>
        @endif

        <p class="text-sm text-gray-500 leading-relaxed">
            Если письмо не пришло — проверьте папку «Спам» или закажите новую ссылку.
        </p>

        <div class="mt-8 space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold text-sm shadow-sm hover:shadow transition">
                    Отправить ссылку ещё раз
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full py-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 font-medium text-sm transition">
                    Выйти
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>