<?php

// Добавить в routes/web.php внутри группы middleware('web')
// ---------------------------------------------------------------------------
// Переключение языка
// ---------------------------------------------------------------------------

use Illuminate\Support\Facades\Route;

Route::post('/locale/{locale}', function (string $locale) {
    $supported = ['ru', 'en'];
    if (in_array($locale, $supported)) {
        session(['locale' => $locale]);
        return back()->withCookie(cookie()->forever('locale', $locale));
    }
    return back();
})->name('locale.switch');
