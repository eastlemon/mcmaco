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
                    <div x-data="{ open: false }" class="relative">
                        {{-- Trigger Button with Border --}}
                        <button @click="open = !open"
                                @click.outside="open = false"
                                class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all duration-200 group border border-gray-200 hover:border-amber-300 shadow-sm hover:shadow">
                            <span class="relative inline-flex">
                                @if(Auth::user()->avatar)
                                    <img src="{{ Auth::user()->avatar }}"
                                        alt="{{ Auth::user()->name }}"
                                        class="w-7 h-7 rounded-full object-cover ring-2 ring-gray-200 group-hover:ring-amber-400 transition-all">
                                @else
                                    <span class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-xs font-bold ring-2 ring-gray-200 group-hover:ring-amber-400 transition-all">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                @endif
                                <span class="absolute bottom-0 right-0 w-2 h-2 bg-green-500 rounded-full ring-1.5 ring-white"></span>
                            </span>
                            <span class="hidden sm:inline font-medium max-w-[100px] truncate">
                                {{ Auth::user()->name }}
                            </span>
                            <svg class="hidden sm:block w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
                                :class="{ 'rotate-180': open }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="transform opacity-0 -translate-y-2 scale-95"
                            x-transition:enter-end="transform opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="transform opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="transform opacity-0 -translate-y-2 scale-95"
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-100/80 z-50 overflow-hidden">

                            {{-- User Info Header --}}
                            <div class="px-4 py-3 bg-gradient-to-r from-amber-50/50 to-white border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        @if(Auth::user()->avatar)
                                            <img src="{{ Auth::user()->avatar }}"
                                                alt="{{ Auth::user()->name }}"
                                                class="w-10 h-10 rounded-full object-cover ring-2 ring-amber-200">
                                        @else
                                            <span class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-sm font-bold ring-2 ring-amber-200">
                                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Menu Items --}}
                            <div class="py-1">
                                {{-- Profile --}}
                                <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors duration-150 group">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-amber-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>{{ __('auth.profile') }}</span>
                                </a>

                                {{-- My Listings --}}
                                <a href="{{ route('ads.manage.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors duration-150 group">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-amber-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <span>{{ __('ads.my_listings') }}</span>
                                    @if($myAdsCount ?? 0 > 0)
                                        <span class="ml-auto bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                            {{ $myAdsCount }}
                                        </span>
                                    @endif
                                </a>

                                {{-- Messages --}}
                                <a href="{{ route('chats.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors duration-150 group">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-amber-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>{{ __('chat.messages') }}</span>
                                    @if($unreadMessagesCount ?? 0 > 0)
                                        <span class="ml-auto bg-red-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1.5 rounded-full flex items-center justify-center">
                                            {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                                        </span>
                                    @endif
                                </a>

                                {{-- Favorites --}}
                                <a href="{{ route('favorites.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors duration-150 group">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-amber-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <span>{{ __('ads.favorites') }}</span>
                                    @if($favoritesCount ?? 0 > 0)
                                        <span class="ml-auto bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                            {{ $favoritesCount }}
                                        </span>
                                    @endif
                                </a>

                                {{-- Divider --}}
                                <div class="border-t border-gray-100 my-1"></div>

                                {{-- Logout --}}
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit"
                                            class="flex items-center gap-3 w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150 group">
                                        <svg class="w-4 h-4 text-red-400 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>{{ __('auth.sign_out') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Guest buttons --}}
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}"
                        class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all duration-200 hidden sm:inline-block border border-transparent hover:border-gray-200">
                            {{ __('auth.login') }}
                        </a>
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}"
                            class="text-sm bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition-all duration-200 shadow-sm hover:shadow font-medium hidden sm:inline-block">
                                {{ __('auth.register') }}
                            </a>
                        @endif
                    </div>
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
               class="flex items-center gap-0.5 bg-gray-100 dark:bg-gray-800/50 rounded-lg p-0.5 shrink-0 px-3 py-1 text-sm font-medium {{ request()->routeIs('ads.index') && !request('category_id') ? 'text-amber-600' : 'text-gray-600 hover:text-amber-600' }} transition">
                {{ __('ads.all_items') }}
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
                    <a href="{{ route('ads.index') }}" class="text-sm px-3 py-1 rounded-full {{ !request('category_id') ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ __('common.all') }}</a>
                    @foreach($rootCategories as $cat)
                        <a href="{{ $cat->slug ? route('categories.show', $cat->slug) : route('ads.index', ['category_id' => $cat->id]) }}" class="text-sm px-3 py-1 rounded-full {{ request('category_id') == $cat->id ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif

            @auth
                <a href="{{ route('profile.edit') }}" class="block text-sm text-gray-600 py-1">{{ __('auth.profile') }}</a>
                <a href="{{ route('ads.manage.index') }}" class="block text-sm text-gray-600 py-1">{{ __('ads.my_ads') }}</a>
                <a href="{{ route('chats.index') }}" class="block text-sm text-gray-600 py-1">{{ __('chat.messages') }}</a>
                <a href="{{ route('favorites.index') }}" class="block text-sm text-gray-600 py-1">{{ __('ads.favorites') }}</a>
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
