<footer class="bg-gray-800 text-gray-300 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">

            {{-- О магазине --}}
            <div>
                <h3 class="text-white font-semibold mb-3">mcmaco</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    {{ __('common.site_tagline') }}.
                </p>
            </div>

            {{-- Каталог --}}
            <div>
                <h4 class="text-white font-medium text-sm mb-3">{{ __('footer.catalog') }}</h4>
                <ul class="space-y-2 text-sm">
                    @php $rootCats = \App\Models\Category::roots()->limit(6)->get(); @endphp
                    @foreach($rootCats as $cat)
                        <li><a href="{{ $cat->slug ? route('categories.show', $cat->slug) : route('ads.index', ['category_id' => $cat->id]) }}" class="hover:text-amber-400 transition">{{ $cat->name }}</a></li>
                    @endforeach
                    <li><a href="{{ route('ads.index') }}" class="hover:text-amber-400 transition">{{ __('ads.all_items') }}</a></li>
                </ul>
            </div>

            {{-- Покупателям --}}
            <div>
                <h4 class="text-white font-medium text-sm mb-3">{{ __('footer.buyers') }}</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('cart') }}" class="hover:text-amber-400 transition">{{ __('shop.cart') }}</a></li>
                    <li><a href="{{ route('checkout.index') }}" class="hover:text-amber-400 transition">{{ __('shop.checkout') }}</a></li>
                    <li><span class="text-gray-500">{{ __('footer.delivery_russia') }}</span></li>
                    <li><span class="text-gray-500">{{ __('footer.payment_methods') }}</span></li>
                </ul>
            </div>

            {{-- Контакты --}}
            <div>
                <h4 class="text-white font-medium text-sm mb-3">{{ __('footer.contacts') }}</h4>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>+7 (800) 000-00-00</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>info@mcmaco.ru</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ __('footer.russia') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-700 mt-8 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-500">© {{ date('Y') }} mcmaco. {{ __('footer.rights') }}</p>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span>{{ __('footer.payment') }}:</span>
                <span class="bg-gray-700 px-2 py-1 rounded text-gray-300">ЮKassa</span>
                <span class="bg-gray-700 px-2 py-1 rounded text-gray-300">{{ __('shop.payment_on_delivery') }}</span>
            </div>
        </div>
    </div>
</footer>