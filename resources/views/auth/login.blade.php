<x-guest-layout>
    @section('title', __('auth.login') . ' — mcmaco')
    <h1 class="text-3xl font-extrabold tracking-tight">{{ __('auth.login') }}</h1>
    <p class="mt-1.5 text-sm text-gray-400">{{ __('auth.login_subtitle') }}</p>

    <!-- Session Status -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('auth.email') }}</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                    <svg class="w-5 h-5" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="block w-full rounded-xl border-gray-200 bg-white pl-10 pr-3.5 py-2.5 text-sm shadow-sm placeholder-gray-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 transition" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        {{-- Пароль --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-gray-700">{{ __('auth.password') }}</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                        Забыли пароль?
                    </a>
                @endif
            </div>
            <div class="relative" x-data="{ visible: false }">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                    <svg class="w-5 h-5" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                </span>
                <input id="password" x-bind:type="visible ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       class="block w-full rounded-xl border-gray-200 bg-white pl-10 pr-11 py-2.5 text-sm shadow-sm placeholder-gray-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 transition" />
                <button type="button" @click="visible = !visible"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                        aria-label="{{ __('auth.show_password') }}">
                    <svg x-show="!visible" class="w-5 h-5" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <svg x-show="visible" x-cloak class="w-5 h-5" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        {{-- Запомнить меня --}}
        <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded-md border-gray-300 text-amber-600 focus:ring-amber-500/40" />
            <span class="text-sm text-gray-500">{{ __('auth.remember_me') }}</span>
        </label>

        <button type="submit"
                class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold text-sm shadow-sm hover:shadow transition">
            {{ __('auth.login') }}
        </button>
    </form>

    <p class="mt-8 text-sm text-center text-gray-500">
        Нет аккаунта?
        <a href="{{ route('register') }}" class="font-semibold text-amber-600 hover:text-amber-700 transition">{{ __('auth.register') }}</a>
    </p>
</x-guest-layout>