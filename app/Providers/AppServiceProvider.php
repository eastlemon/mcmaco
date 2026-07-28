<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);

        // Share root categories with all views (single query)
        View::composer(['layouts.navigation', 'layouts.footer', 'ads.index'], function ($view) {
            $rootCategories = cache()->rememberForever('nav_categories', function () {
                return Category::roots()->withCount('ads')->get();
            });
            $view->with('rootCategories', $rootCategories);
        });
    }
}
