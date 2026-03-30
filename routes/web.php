<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\AdImageController;
use App\Http\Controllers\AdManageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AdController::class, 'index'])->name('ads.index');
Route::get('/ads/{ad}', [AdController::class, 'show'])->name('ads.show');

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
});

require __DIR__.'/auth.php';
