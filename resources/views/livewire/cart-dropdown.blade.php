<div x-data="{ open: false }" @cart-updated.window="open = true; setTimeout(() => open = false, 3000)"
     class="relative">
    <button @click="open = !open" class="relative flex items-center gap-1.5 text-sm hover:text-amber-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span class="hidden sm:inline">Корзина</span>
        @if($itemsCount > 0)
            <span class="absolute -top-2 -right-2 bg-amber-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-medium">
                {{ $itemsCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-transition
         @click.outside="open = false"
         class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-xl border border-gray-100 z-50 p-4">
        <div class="text-sm font-medium mb-3 text-gray-700">Корзина</div>
        @if($itemsCount > 0)
            <div class="text-xs text-gray-500 mb-3">{{ $itemsCount }} товар(ов) · {{ number_format($total, 0, ',', ' ') }} ₽</div>
            <a href="{{ route('cart') }}" class="block text-center bg-amber-600 text-white text-sm py-2 rounded-lg hover:bg-amber-700 transition">
                Перейти в корзину
            </a>
        @else
            <div class="text-sm text-gray-400 text-center py-3">Корзина пуста</div>
        @endif
    </div>
</div>