<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap:generate', function () {
    $this->call(\App\Console\Commands\GenerateSitemap::class);
})->purpose('Generate sitemap.xml for active ads');

// Sitemap regeneration — daily at 03:00
Schedule::command('sitemap:generate')->dailyAt('03:00')->description('Regenerate sitemap.xml');
