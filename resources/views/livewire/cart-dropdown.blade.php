{{-- resources/views/livewire/cart-dropdown.blade.php --}}
<div x-data="{ open: false }"
     @click.outside="open = false"
     class="relative">

    {{-- Cart Button --}}
    <button @click="open = !open"
            class="relative flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all duration-200 group border border-gray-200 hover:border-amber-300 shadow-sm hover:shadow">
        <span class="relative inline-flex">
            {{-- Cart Icon --}}
            <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </span>

        {{-- Cart Label --}}
        <span class="hidden sm:inline font-medium">{{ __('shop.cart') }}</span>

        {{-- Text Badge --}}
        @if($itemsCount > 0)
            <span class="hidden sm:inline-flex items-center justify-center min-w-[18px] h-[18px] px-1.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full">
                {{ $itemsCount }}
            </span>
        @endif
    </button>

    {{-- Cart Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="transform opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="transform opacity-0 -translate-y-2 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100/80 z-50 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-amber-50/50 to-white border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-sm font-semibold text-gray-800">{{ __('shop.shopping_cart') }}</span>
            </div>
            <div class="flex items-center gap-2">
                @if($itemsCount > 0)
                    <span class="text-xs bg-amber-600 text-white px-2.5 py-1 rounded-full font-medium shadow-sm">
                        {{ $itemsCount }} {{ __('shop.items') }}
                    </span>
                @endif
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-4 max-h-[400px] overflow-y-auto">
            @if($itemsCount > 0)
                {{-- Items --}}
                <div class="space-y-2">
                    @foreach($items as $item)
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors group">
                            {{-- Product Image --}}
                            <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if($item['image'])
                                    <img src="{{ asset('storage/' . $item['image']) }}"
                                         alt="{{ $item['name'] }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Product Details --}}
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('ads.show', $item['slug']) }}"
                                   class="text-sm font-medium text-gray-800 hover:text-amber-600 transition truncate block">
                                    {{ $item['name'] }}
                                </a>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span>{{ $item['quantity'] }} × {{ number_format($item['price'], 0, ',', ' ') }} ₽</span>
                                    <span class="text-gray-300">•</span>
                                    <span class="text-amber-600 font-medium">{{ number_format($item['subtotal'], 0, ',', ' ') }} ₽</span>
                                </div>
                            </div>

                            {{-- Remove Button --}}
                            <button wire:click="removeItem({{ $item['id'] }})"
                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-red-500 rounded hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                {{-- Footer --}}
                <div class="mt-4 pt-3 border-t border-gray-100 space-y-3">
                    {{-- Subtotal --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ __('shop.subtotal') }}</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($total, 0, ',', ' ') }} ₽</span>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('cart') }}"
                           class="text-center bg-gray-100 text-gray-700 text-sm py-2.5 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                            {{ __('shop.view_cart') }}
                        </a>
                        <a href="{{ route('cart') }}"
                           class="text-center bg-amber-600 text-white text-sm py-2.5 rounded-lg hover:bg-amber-700 transition duration-200 font-medium shadow-sm hover:shadow">
                            {{ __('shop.go_to_cart') }}
                            <span class="ml-1">→</span>
                        </a>
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-10">
                    <div class="w-20 h-20 mx-auto mb-4 bg-gray-50 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600">{{ __('shop.your_cart_is_empty') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ __('shop.start_shopping') }}</p>
                    <a href="{{ route('ads.index') }}"
                       class="inline-block mt-4 text-sm text-amber-600 hover:text-amber-700 font-medium">
                        {{ __('shop.browse_products') }} →
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
