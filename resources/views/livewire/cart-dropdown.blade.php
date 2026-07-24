<div x-data="{ open: false }" @cart-updated.window="open = true; setTimeout(() => open = false, 2000)"
     class="relative">
    <button @click="open = !open" class="relative flex items-center gap-1 text-sm hover:text-amber-600">
        🛒
        <span class="absolute -top-2 -right-2 bg-amber-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
            {{ $itemsCount }}
        </span>
    </button>

    <div x-show="open" x-transition @click.outside="open = false"
         class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border z-50 p-4">
        <div class="text-sm font-medium mb-2">Корзина</div>
        @if($itemsCount > 0)
            <div class="text-xs text-gray-500 mb-3">{{ $itemsCount }} товар(ов) · {{ number_format($total, 0, ',', ' ') }} ₽</div>
            <a href="/cart" class="block text-center bg-amber-600 text-white text-sm py-2 rounded hover:bg-amber-700">Перейти в корзину</a>
        @else
            <div class="text-sm text-gray-400 text-center py-3">Корзина пуста</div>
        @endif
    </div>
</div>
