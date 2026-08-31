<x-guest-layout>
    @section('title', __('auth.forgot_title') . ' — mcmaco')
    <h1 class="text-3xl font-extrabold tracking-tight">{{ __('auth.forgot_title') }}</h1>
    <p class="mt-1.5 text-sm text-gray-400">{{ __('auth.forgot_hint') }}</p>

    <!-- Session Status -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                    <svg class="w-5 h-5" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="block w-full rounded-xl border-gray-200 bg-white pl-10 pr-3.5 py-2.5 text-sm shadow-sm placeholder-gray-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 transition" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <button type="submit"
                class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold text-sm shadow-sm hover:shadow transition">
            {{ __('auth.send_reset_link') }}
        </button>
    </form>

    <p class="mt-8 text-sm text-center text-gray-500">
        <a href="{{ route('login') }}" class="font-semibold text-amber-600 hover:text-amber-700 transition">← {{ __('auth.back_to_login') }}</a>
    </p>
</x-guest-layout>