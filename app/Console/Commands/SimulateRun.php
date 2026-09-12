<?php

namespace App\Console\Commands;

use App\Enums\AppMode;
use App\Services\SimulationEngine;
use Illuminate\Console\Command;

/**
 * Run one tick of the mcmaco simulation.
 *
 * Generates a mix of fake visits, chat messages and orders based on the
 * configured intensity preset. All generated records are marked with
 * is_simulated=true so they never contaminate real production data.
 *
 * Schedule:
 *   In production, this command runs every 5 minutes via Laravel's
 *   scheduler (see routes/console.php). For local dev / demo, run
 *   manually:
 *
 *     php artisan mcmaco:simulate:run
 *     php artisan mcmaco:simulate:run --intensity=high
 *     php artisan mcmaco:simulate:run --count=3   (run 3 ticks in a row)
 */
class SimulateRun extends Command
{
    protected $signature = 'mcmaco:simulate:run
                            {--intensity= : Force intensity (low|medium|high), overrides config}
                            {--count=1 : How many ticks to run in a row}
                            {--force : Run even if simulation mode is off}';

    protected $description = 'Generate one tick of simulated bot activity (visits, chats, orders)';

    public function handle(SimulationEngine $engine): int
    {
        // Safety: refuse to run unless simulation mode is on OR --force was passed.
        if (! AppMode::active()->isSimulation() && ! $this->option('force')) {
            $this->warn('Simulation mode is OFF (MCMACO_MODE not set to simulation/dual).');
            $this->warn('Pass --force to run anyway, or set MCMACO_MODE=simulation in .env');
            return self::FAILURE;
        }

        $intensityKey = (string) ($this->option('intensity') ?: config('simulation.default_intensity', 'medium'));
        $intensityKey = in_array($intensityKey, ['low', 'medium', 'high'], true) ? $intensityKey : 'medium';

        $intensity = config("simulation.intensity.{$intensityKey}");
        if (! is_array($intensity)) {
            $this->error("Invalid intensity preset: {$intensityKey}");
            return self::FAILURE;
        }

        $ticks = max(1, (int) $this->option('count'));

        $this->info("mcmaco simulation — intensity={$intensityKey}, ticks={$ticks}");
        $this->info("Each tick: {$intensity['visits_per_run']} visits, {$intensity['chats_per_run']} chats, {$intensity['orders_per_run']} orders.");

        $totals = ['visits' => 0, 'chats' => 0, 'orders' => 0];

        $bar = $this->output->createProgressBar($ticks);
        $bar->start();

        foreach (range(1, $ticks) as $_) {
            $result = $engine->tick($intensity);
            $totals['visits'] += $result['visits'];
            $totals['chats']  += $result['chats'];
            $totals['orders'] += $result['orders'];
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info(sprintf(
            "Done. Total events: %d visits, %d chats, %d orders.",
            $totals['visits'],
            $totals['chats'],
            $totals['orders']
        ));

        return self::SUCCESS;
    }
}
