@extends('layouts.app')

@section('meta_title', 'Оформление заказа — mcmaco')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-400 mb-4">
            <a href="{{ route('ads.index') }}" class="hover:text-amber-600">Главная</a>
            <span class="mx-1">/</span>
            <a href="{{ route('cart') }}" class="hover:text-amber-600">{{ __('shop.cart') }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-600">Оформление заказа</span>
        </nav>

        <h1 class="text-2xl font-bold mb-6 text-gray-800">Оформление заказа</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- Форма --}}
            <form method="POST" action="/checkout" id="checkout-form">
                @csrf
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                    <h2 class="font-semibold text-lg text-gray-800">Контактные данные</h2>

                    <div>
                        <label class="text-sm text-gray-500">Имя *</label>
                        <input name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}"
                               class="w-full border rounded-lg px-3 py-2 mt-1 @error('customer_name') border-red-500 @enderror">
                        @error('customer_name') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-gray-500">Телефон *</label>
                        <input name="customer_phone" type="tel" placeholder="+7 (___) ___-__-__"
                               class="w-full border rounded-lg px-3 py-2 mt-1 @error('customer_phone') border-red-500 @enderror">
                        @error('customer_phone') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-gray-500">Email</label>
                        <input name="customer_email" type="email" value="{{ old('customer_email', auth()->user()?->email) }}"
                               class="w-full border rounded-lg px-3 py-2 mt-1">
                    </div>

                    <h2 class="font-semibold text-lg text-gray-800 pt-2">Способ доставки</h2>

                    <div class="space-y-2">
                        @foreach($deliveryOptions as $option)
                            @php $method = $option['method']; @endphp
                            <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition {{ old('delivery_method_id') == $method->id ? 'border-amber-500 bg-amber-50' : '' }}"
                                   data-type="{{ $method->type }}"
                                   onclick="updateDelivery(this, {{ $option['cost'] }})">
                                <input type="radio" name="delivery_method_id" value="{{ $method->id }}"
                                       class="mt-1 text-amber-600 focus:ring-amber-500"
                                       @checked(old('delivery_method_id') == $method->id || ($loop->first && !old('delivery_method_id')))>
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-800">{{ $method->name }}</div>
                                    @if($method->description)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $method->description }}</div>
                                    @endif
                                </div>
                                <div class="text-sm font-medium {{ $option['cost'] === 0 ? 'text-green-600' : 'text-gray-700' }}">
                                    {{ $option['cost'] === 0 ? 'Бесплатно' : number_format($option['cost'], 0, ',', ' ') . ' ₽' }}
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div id="address-field">
                        <label class="text-sm text-gray-500">Адрес доставки</label>
                        <textarea name="delivery_address" rows="2"
                                  class="w-full border rounded-lg px-3 py-2 mt-1 @error('delivery_address') border-red-500 @enderror"
                                  placeholder="Город, улица, дом, квартира">{{ old('delivery_address') }}</textarea>
                        @error('delivery_address') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-gray-500">Комментарий к заказу</label>
                        <textarea name="comment" rows="2"
                                  class="w-full border rounded-lg px-3 py-2 mt-1">{{ old('comment') }}</textarea>
                    </div>
                </div>

                <button type="submit" name="pay_online" value="1" class="w-full bg-green-600 text-white font-medium py-3 rounded-lg mt-4 hover:bg-green-700 transition">
                    💳 Оплатить онлайн
                </button>
                <button type="submit" class="w-full bg-amber-600 text-white font-medium py-3 rounded-lg mt-2 hover:bg-amber-700 transition">
                    Подтвердить заказ (оплата при получении)
                </button>
            </form>

            {{-- Сводка --}}
            <div>
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-20">
                    <h2 class="font-semibold text-lg text-gray-800 mb-4">Ваш заказ</h2>
                    <div class="space-y-2 mb-4">
                        @foreach($items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ $item->ad->title }} ×{{ $item->qty }}</span>
                                <span>{{ number_format($item->ad->price * $item->qty, 0, ',', ' ') }} ₽</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t pt-3 space-y-1">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Товары</span>
                            <span>{{ number_format($total, 0, ',', ' ') }} ₽</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Доставка</span>
                            <span id="delivery-cost-label">—</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-800">
                            <span>Итого</span>
                            <span id="grand-total">{{ number_format($total, 0, ',', ' ') }} ₽</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const itemsTotal = {{ $total }};
const deliveryLabels = {
    @foreach($deliveryOptions as $option)
    {{ $option['method']->id }}: {{ $option['cost'] }},
    @endforeach
};

function updateDelivery(label, cost) {
    // Update visual selection
    document.querySelectorAll('label[data-type]').forEach(l => {
        l.classList.remove('border-amber-500', 'bg-amber-50');
    });
    label.classList.add('border-amber-500', 'bg-amber-50');

    // Update cost display
    const deliveryLabel = document.getElementById('delivery-cost-label');
    const grandTotal = document.getElementById('grand-total');

    if (cost === 0) {
        deliveryLabel.textContent = 'Бесплатно';
        deliveryLabel.classList.add('text-green-600');
    } else {
        deliveryLabel.textContent = cost.toLocaleString('ru-RU') + ' ₽';
        deliveryLabel.classList.remove('text-green-600');
    }

    grandTotal.textContent = (itemsTotal + cost).toLocaleString('ru-RU') + ' ₽';

    // Show/hide address field
    const isPickup = label.dataset.type === 'pickup';
    document.getElementById('address-field').style.display = isPickup ? 'none' : '';
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('input[name="delivery_method_id"]:checked');
    if (checked) {
        const label = checked.closest('label');
        const cost = deliveryLabels[checked.value] || 0;
        updateDelivery(label, cost);
    }
});
</script>
@endsection