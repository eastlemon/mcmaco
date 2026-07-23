<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartService
{
    public function getOrCreateCart(Request $request): Cart
    {
        $sessionId = $request->session()->getId();

        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart && $request->user()) {
            $cart = Cart::where('user_id', $request->user()->id)->latest()->first();
        }

        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sessionId,
                'user_id' => $request->user()?->id,
            ]);
        }

        // Bind to user if logged in
        if ($request->user() && !$cart->user_id) {
            $cart->update(['user_id' => $request->user()->id]);
        }

        return $cart;
    }

    public function add(Cart $cart, int $adId, int $qty = 1): CartItem
    {
        $ad = Ad::active()->inStock()->findOrFail($adId);

        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'ad_id' => $adId,
        ]);

        $item->qty = min($item->qty + $qty, $ad->stock);
        $item->save();

        return $item;
    }

    public function updateQty(Cart $cart, int $adId, int $qty): void
    {
        $item = $cart->items()->where('ad_id', $adId)->firstOrFail();
        $ad = $item->ad;

        if ($qty <= 0) {
            $item->delete();
            return;
        }

        $item->update(['qty' => min($qty, $ad->stock)]);
    }

    public function remove(Cart $cart, int $adId): void
    {
        $cart->items()->where('ad_id', $adId)->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }
}