<nav x-data="{ open: false, catOpen: false }" class="bg-white border-b border-gray-100 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 gap-4">

            {{-- Logo --}}
            <div class="shrink-0 flex items-center">
                <a href="{{ route('ads.index') }}" class="text-xl font-bold text-amber-600">
                    mcmaco
                </a>
            </div>

            {{-- Search (desktop) --}}
            <form action="{{ route('ads.index') }}" method="GET" class="hidden md:flex flex-1 max-w-xl">
                <div class="relative w-full">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="{{ __('common.search_placeholder') }}"
                           class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-gray-50">
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>

            {{-- Right actions --}}
            <div class="flex items-center gap-3">
                <x-locale-switcher />

                {{-- Cart --}}
                <livewire:cart-dropdown />

                {{-- Auth dropdown --}}
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="hidden sm:inline">{{ \Illuminate\Support\Str::limit(Auth::user()->name, 15) }}</span>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Профиль</x-dropdown-link>
                            <x-dropdown-link :href="route('ads.manage.index')">Мои товары</x-dropdown-link>
                            <x-dropdown-link :href="route('chats.index')">Сообщения</x-dropdown-link>
                            <x-dropdown-link :href="route('favorites.index')">Избранное</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('auth.logout') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 hidden sm:inline">{{ __('auth.login') }}</a>
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="text-sm bg-amber-600 text-white px-3 py-1.5 rounded hover:bg-amber-700 hidden sm:inline">{{ __('auth.register') }}</a>
                    @endif
                @endauth

                {{-- Mobile menu button --}}
                <button @click="open = !open" class="sm:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Secondary row: categories (desktop) --}}
        @if($rootCategories->isNotEmpty())
        <div class="hidden sm:flex items-center gap-1 h-10 border-t border-gray-50 overflow-x-auto scrollbar-hide">
            <a href="{{ route('ads.index') }}"
               class="shrink-0 px-3 py-1 text-sm font-medium {{ request()->routeIs('ads.index') && !request('category_id') ? 'text-amber-600' : 'text-gray-600 hover:text-amber-600' }} transition">
                Все товары
            </a>
            @foreach($rootCategories as $cat)
                <a href="{{ $cat->slug ? route('categories.show', $cat->slug) : route('ads.index', ['category_id' => $cat->id]) }}"
                   class="shrink-0 px-3 py-1 text-sm {{ request('category_id') == $cat->id ? 'text-amber-600 font-medium' : 'text-gray-600 hover:text-amber-600' }} transition">
                    {{ $cat->name }}
                    @if($cat->ads_count > 0)
                        <span class="text-xs text-gray-400">{{ $cat->ads_count }}</span>
                    @endif
                </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Mobile menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-gray-100">
        <div class="px-4 py-3 space-y-3">
            {{-- Mobile search --}}
            <form action="{{ route('ads.index') }}" method="GET" class="relative">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="{{ __('common.search_placeholder') }}"
                       class="w-full border rounded-lg pl-10 pr-4 py-2 text-sm bg-gray-50">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </form>

            {{-- Mobile categories --}}
            @if($rootCategories->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('ads.index') }}" class="text-sm px-3 py-1 rounded-full {{ !request('category_id') ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600' }}">Все</a>
                    @foreach($rootCategories as $cat)
                        <a href="{{ $cat->slug ? route('categories.show', $cat->slug) : route('ads.index', ['category_id' => $cat->id]) }}" class="text-sm px-3 py-1 rounded-full {{ request('category_id') == $cat->id ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif

            @auth
                <a href="{{ route('profile.edit') }}" class="block text-sm text-gray-600 py-1">Профиль</a>
                <a href="{{ route('ads.manage.index') }}" class="block text-sm text-gray-600 py-1">Мои товары</a>
                <a href="{{ route('chats.index') }}" class="block text-sm text-gray-600 py-1">Сообщения</a>
                <a href="{{ route('favorites.index') }}" class="block text-sm text-gray-600 py-1">Избранное</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-600 py-1">{{ __('auth.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block text-sm text-gray-600 py-1">{{ __('auth.login') }}</a>
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="block text-sm text-amber-600 py-1">{{ __('auth.register') }}</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
