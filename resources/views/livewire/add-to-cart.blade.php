<div>
    <button wire:click="addToCart" class="{{ $class }}">
        {{ $label }}
    </button>
    <div wire:loading class="text-xs text-gray-400 mt-1">Добавляем...</div>
</div>