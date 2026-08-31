<x-guest-layout>
    @section('title', __('auth.reset_title') . ' — mcmaco')
    <h1 class="text-3xl font-extrabold tracking-tight">{{ __('auth.reset_title') }}</h1>
    <p class="mt-1.5 text-sm text-gray-400">{{ __('auth.reset_subtitle') }}</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('auth.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="block w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 transition" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('auth.password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="block w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 transition" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('auth.password_confirm') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="block w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 transition" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button type="submit"
                class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold text-sm shadow-sm hover:shadow transition">
            {{ __('auth.save_password') }}
        </button>
    </form>
</x-guest-layout>