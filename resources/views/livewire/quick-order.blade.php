<div>
    @if(session('quick-order-success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
             class="bg-green-100 border border-green-300 text-green-700 rounded-lg p-3 text-sm mb-3">
            ✅ {{ session('quick-order-success') }}
        </div>
    @endif

    <button onclick="document.getElementById('quick-order-modal-{{ $adId }}').showModal()"
            class="w-full border-2 border-amber-600 text-amber-700 hover:bg-amber-50 font-medium py-3 rounded-lg transition">
        ⚡ Купить в 1 клик
    </button>

    <dialog id="quick-order-modal-{{ $adId }}" class="rounded-xl p-0 backdrop:bg-black/50">
        <div class="p-6 max-w-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold">Быстрый заказ</h3>
                <button onclick="document.getElementById('quick-order-modal-{{ $adId }}').close()" class="text-gray-400 text-xl">✕</button>
            </div>

            <p class="text-sm text-gray-500 mb-4">Оставьте телефон — перезвоним для подтверждения.</p>

            <form wire:submit="submit" class="space-y-3">
                <div>
                    <input wire:model="name" type="text" placeholder="Ваше имя"
                           class="w-full border rounded-lg px-3 py-2 @error('name') border-red-500 @enderror">
                    @error('name') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                </div>
                <div>
                    <input wire:model="phone" type="tel" placeholder="+7 (___) ___-__-__"
                           class="w-full border rounded-lg px-3 py-2 @error('phone') border-red-500 @enderror">
                    @error('phone') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="w-full bg-amber-600 text-white py-3 rounded-lg font-medium hover:bg-amber-700">
                    Оформить заказ
                </button>
            </form>
        </div>
    </dialog>
</div>