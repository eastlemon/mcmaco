<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test for the mcmaco:simulate:run command — verifies the CLI
 * surface (signature, options, exit codes) without asserting exact
 * event counts (those are covered by SimulationIsolationTest).
 */
class SimulateRunCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_refuses_when_simulation_off(): void
    {
        config()->set('simulation.mode', 'real');

        $this->artisan('mcmaco:simulate:run')
            ->expectsOutputToContain('Simulation mode is OFF')
            ->assertFailed();
    }

    public function test_command_runs_with_force_even_when_off(): void
    {
        config()->set('simulation.mode', 'real');

        // Need at least 1 bot + 1 active ad for the engine to produce events
        User::factory()->count(2)->simulated()->create();
        Ad::factory()->create(['status' => 'active', 'stock' => 5]);

        $this->artisan('mcmaco:simulate:run', ['--force' => true])
            ->assertSuccessful();
    }

    public function test_command_runs_in_simulation_mode(): void
    {
        config()->set('simulation.mode', 'simulation');

        User::factory()->count(2)->simulated()->create();
        Ad::factory()->create(['status' => 'active', 'stock' => 5]);

        $this->artisan('mcmaco:simulate:run')
            ->assertSuccessful();
    }

    public function test_command_accepts_intensity_option(): void
    {
        config()->set('simulation.mode', 'simulation');

        User::factory()->count(5)->simulated()->create();
        Ad::factory()->count(3)->create(['status' => 'active', 'stock' => 10]);

        $this->artisan('mcmaco:simulate:run', [
            '--intensity' => 'high',
            '--count'     => 2,
        ])->assertSuccessful();
    }

    public function test_command_rejects_invalid_intensity(): void
    {
        config()->set('simulation.mode', 'simulation');

        $this->artisan('mcmaco:simulate:run', ['--intensity' => 'banana'])
            ->assertFailed();
    }
}
