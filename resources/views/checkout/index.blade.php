@extends('layouts.app')

@section('meta_title', 'Оформление заказа — mcmaco')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold mb-6">Оформление заказа</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- Форма --}}
            <form method="POST" action="/checkout">
                @csrf
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                    <h2 class="font-semibold text-lg">Контактные данные</h2>

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

                    <h2 class="font-semibold text-lg pt-2">Доставка</h2>

                    <div>
                        <select name="delivery_method" id="delivery_method" class="w-full border rounded-lg px-3 py-2 mt-1">
                            @foreach($deliveryMethods as $value => $label)
                                <option value="{{ $value }}" @selected(old('delivery_method') === $value)>
                                    {{ $label }}@if($value !== 'pickup') (+{{ $value === 'cdek' ? 350 : ($value === 'post' ? 250 : 400) }} ₽)@endif
                                </option>
                            @endforeach
                        </select>
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

                <button type="submit" name="pay_online" value="1" class="w-full bg-green-600 text-white font-medium py-3 rounded-lg mt-4 hover:bg-green-700">
                    💳 Оплатить онлайн
                </button>
                <button type="submit" class="w-full bg-amber-600 text-white font-medium py-3 rounded-lg mt-2 hover:bg-amber-700">
                    Подтвердить заказ (оплата при получении)
                </button>
            </form>

            {{-- Сводка --}}
            <div>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-semibold text-lg mb-4">Ваш заказ</h2>
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
                        <div class="flex justify-between text-lg font-bold">
                            <span>Итого</span>
                            <span>{{ number_format($total, 0, ',', ' ') }} ₽</span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400 mt-3">Стоимость доставки будет рассчитана после подтверждения.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('delivery_method').addEventListener('change', function() {
    document.getElementById('address-field').style.display = this.value === 'pickup' ? 'none' : '';
});
document.getElementById('delivery_method').dispatchEvent(new Event('change'));
</script>
@endsection