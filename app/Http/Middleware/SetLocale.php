<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supported = ['ru', 'en'];
        $locale = $request->cookie('locale', session('locale', config('app.locale')));

        App::setLocale(in_array($locale, $supported) ? $locale : 'ru');

        return $next($request);
    }
}
