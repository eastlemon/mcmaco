<?php

namespace Tests\Feature;

use App\Enums\AppMode;
use App\Models\Ad;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Order;
use App\Models\SimulatedEvent;
use App\Models\User;
use App\Services\SimulationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Critical safety tests: simulated records must NEVER pollute real data.
 * If any of these tests fail, the simulation layer is broken and must
 * not be deployed — that's how data leakage between real users and bots
 * would happen.
 */
class SimulationIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_mode_defaults_to_real(): void
    {
        // Fresh app with no MCMACO_MODE env var must default to REAL
        config()->set('simulation.mode', null);
        $this->assertSame(AppMode::REAL, AppMode::active());
    }

    public function test_app_mode_resolves_simulation(): void
    {
        config()->set('simulation.mode', 'simulation');
        $this->assertSame(AppMode::SIMULATION, AppMode::active());
        $this->assertTrue(AppMode::active()->isSimulation());
    }

    public function test_app_mode_invalid_value_falls_back_to_real(): void
    {
        // Defensive: garbage in env must not break the app
        config()->set('simulation.mode', 'banana');
        $this->assertSame(AppMode::REAL, AppMode::active());
    }

    public function test_user_factory_simulated_state_sets_flag(): void
    {
        $realUser    = User::factory()->create();
        $simulatedUser = User::factory()->simulated()->create();

        $this->assertFalse($realUser->is_simulated);
        $this->assertTrue($simulatedUser->is_simulated);
        $this->assertNotNull($simulatedUser->city);
        $this->assertNotNull($simulatedUser->phone);
        $this->assertNotNull($simulatedUser->avatar);
    }

    public function test_real_scope_excludes_simulated_users(): void
    {
        User::factory()->count(3)->create();
        User::factory()->count(2)->simulated()->create();

        $this->assertCount(3, User::real()->get());
        $this->assertCount(2, User::simulated()->get());
        $this->assertCount(5, User::all());
    }

    public function test_engine_creates_simulated_records_marked_correctly(): void
    {
        // Set up: 1 real user, 2 simulated users, 2 in-stock ads
        $seller = User::factory()->create();
        $bots   = User::factory()->count(3)->simulated()->create();
        $ads    = Ad::factory()->count(3)->create([
            'user_id' => $seller->id,
            'status'  => 'active',
            'stock'   => 10,
            'price'   => 1000,
        ]);

        $engine = app(SimulationEngine::class);
        $result = $engine->tick([
            'visits_per_run' => 5,
            'chats_per_run'  => 2,
            'orders_per_run' => 1,
        ]);

        // Visits and orders are deterministic counts
        $this->assertSame(5, $result['visits']);
        $this->assertSame(1, $result['orders']);

        // Chats: firstOrCreate means duplicates are deduped, so the actual count
        // is between 1 and 2 depending on random bot+ad picks. We assert >= 1.
        $this->assertGreaterThanOrEqual(1, $result['chats']);

        // All chats/messages/orders created by engine carry is_simulated=true
        $this->assertSame(0, Chat::where('is_simulated', false)->count());
        $this->assertSame(0, Message::where('is_simulated', false)->count());
        $this->assertSame(0, Order::where('is_simulated', false)->count());

        // All orders created by the engine must be marked is_simulated
        $this->assertSame(1, Order::where('is_simulated', true)->count());

        // SimulatedEvent log: 5 visits + N chats + 1 order. At least 7 events.
        $this->assertGreaterThanOrEqual(7, SimulatedEvent::count());
        $this->assertSame(5, SimulatedEvent::where('type', SimulatedEvent::TYPE_VISIT)->count());
        $this->assertSame(1, SimulatedEvent::where('type', SimulatedEvent::TYPE_ORDER_PLACED)->count());
        $this->assertSame(
            $result['chats'],
            SimulatedEvent::where('type', SimulatedEvent::TYPE_CHAT_MESSAGE)->count()
        );
    }

    public function test_engine_skips_when_no_bots(): void
    {
        // No simulated users exist — engine must skip cleanly, no exceptions
        Ad::factory()->create(['status' => 'active', 'stock' => 5]);

        $engine = app(SimulationEngine::class);
        $result = $engine->tick([
            'visits_per_run' => 5,
            'chats_per_run'  => 2,
            'orders_per_run' => 1,
        ]);

        $this->assertSame(0, $result['visits']);
        $this->assertSame(0, $result['chats']);
        $this->assertSame(0, $result['orders']);
        $this->assertSame(0, SimulatedEvent::count());
    }

    public function test_engine_skips_when_no_active_ads(): void
    {
        // Bots exist but no ads to act on — engine must skip cleanly
        User::factory()->count(3)->simulated()->create();

        $engine = app(SimulationEngine::class);
        $result = $engine->tick([
            'visits_per_run' => 5,
            'chats_per_run'  => 2,
            'orders_per_run' => 1,
        ]);

        $this->assertSame(0, $result['visits'] + $result['chats'] + $result['orders']);
    }

    public function test_simulated_user_seeder_is_idempotent(): void
    {
        config()->set('simulation.bot_count', 5);

        $this->artisan('db:seed', ['--class' => 'SimulatedUserSeeder'])
            ->assertExitCode(0);
        $this->assertSame(5, User::where('is_simulated', true)->count());

        // Run again — must not duplicate
        $this->artisan('db:seed', ['--class' => 'SimulatedUserSeeder'])
            ->assertExitCode(0);
        $this->assertSame(5, User::where('is_simulated', true)->count());
    }
}
