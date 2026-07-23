<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\AdImageController;
use App\Http\Controllers\AdManageController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AdController::class, 'index'])->name('ads.index');
Route::get('/listing/{slug}', [AdController::class, 'show'])->name('ads.show');

// Cart & Checkout
Route::get('/cart', fn () => view('livewire.cart-page'))->name('cart');
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/{order}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('orders.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/my/ads', [AdManageController::class, 'index'])->name('ads.manage.index');
    Route::get('/my/ads/create', [AdManageController::class, 'create'])->name('ads.manage.create');
    Route::post('/my/ads', [AdManageController::class, 'store'])->name('ads.manage.store');
    Route::get('/my/ads/{ad}/edit', [AdManageController::class, 'edit'])->name('ads.manage.edit');
    Route::patch('/my/ads/{ad}', [AdManageController::class, 'update'])->name('ads.manage.update');

    Route::post('/ads/{ad}/images', [AdImageController::class, 'store'])->name('ads.images.store');
    Route::delete('/ads/{ad}/images/{adImage}', [AdImageController::class, 'destroy'])->name('ads.images.destroy');

    Route::get('/my/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::post('/ads/{ad}/chats', [ChatController::class, 'store'])->name('chats.store');
    Route::get('/my/chats/{chat}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/my/chats/{chat}/messages', [MessageController::class, 'store'])->name('messages.store');

    Route::get('/my/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/ads/{ad}/favorite', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/ads/{ad}/favorite', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
});

require __DIR__.'/auth.php';
