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

// Simulation engine — runs every N minutes (default 5) when MCMACO_MODE
// is set to 'simulation' or 'dual'. The command itself refuses to run
// when simulation mode is off, so this schedule is safe to leave enabled
// in production environments where simulation is never active.
$simulationTick = (int) config('simulation.tick_minutes', 5);
$simulationTick = max(5, $simulationTick); // Laravel scheduler minimum is 5 min
Schedule::command('mcmaco:simulate:run')
    ->everyFiveMinutes()
    ->description('Generate one tick of simulated bot activity')
    ->runInBackground();

// Pipeline schedules — registered at boot from DB
// Note: after adding/changing pipeline schedules, run: php artisan schedule:reload
// Wrapped in try/catch: DB may not exist during composer install / package:discover
try {
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
} catch (\Throwable $e) {
    // DB not ready yet (e.g. during composer install / package:discover in Docker build)
}
