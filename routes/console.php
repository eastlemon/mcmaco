<?php

use App\Jobs\RunPipelineJob;
use App\Models\Pipeline;
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

// Pipeline schedules — registered at boot from DB
// Note: after adding/changing pipeline schedules, run: php artisan schedule:reload
Pipeline::whereNotNull('schedule')
    ->where('is_active', true)
    ->each(function (Pipeline $pipeline) {
        Schedule::call(function () use ($pipeline) {
            RunPipelineJob::dispatch($pipeline->fresh());
        })
            ->cron($pipeline->schedule)
            ->name("pipeline:{$pipeline->id}")
            ->description("Pipeline: {$pipeline->name}");
    });
