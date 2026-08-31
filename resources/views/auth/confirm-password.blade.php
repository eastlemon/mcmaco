<x-guest-layout>
    @section('title', __('auth.confirm_title') . ' — mcmaco')
    <h1 class="text-3xl font-extrabold tracking-tight">{{ __('auth.confirm_title') }}</h1>
    <p class="mt-1.5 text-sm text-gray-400">{{ __('auth.confirm_hint') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('auth.password') }}</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                    <svg class="w-5 h-5" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                </span>
                <input id="password" type="password" name="password" required autocomplete="current-password" autofocus
                       class="block w-full rounded-xl border-gray-200 bg-white pl-10 pr-3.5 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 transition" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <button type="submit"
                class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold text-sm shadow-sm hover:shadow transition">
            {{ __('auth.confirm') }}
        </button>
    </form>
</x-guest-layout>