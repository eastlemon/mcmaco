<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates a pool of fake "buyer" accounts for simulation mode.
 *
 * These users have is_simulated=true — they will never appear in real
 * admin reports (unless explicitly included via ->simulated() scope).
 * They have realistic Russian names, cities, phone numbers and DiceBear
 * avatars so chats and orders look natural in the admin panel.
 *
 * The seeder is idempotent: running it twice won't duplicate users
 * (checked by email domain pattern).
 */
class SimulatedUserSeeder extends Seeder
{
    private const BOT_EMAIL_DOMAIN = 'bot.mcmaco.local';

    public function run(): void
    {
        $count = (int) config('simulation.bot_count', 50);

        // Idempotency: if we already have the target number of bots, skip.
        $existing = User::query()->where('is_simulated', true)->count();
        if ($existing >= $count) {
            $this->command->info("SimulatedUserSeeder: {$existing} bots already exist, skipping.");
            return;
        }

        $toCreate = $count - $existing;
        $this->command->info("SimulatedUserSeeder: creating {$toCreate} fake buyers...");

        User::factory()
            ->count($toCreate)
            ->simulated()
            ->sequence(fn ($sequence) => [
                // Predictable email pattern so re-runs don't create duplicates
                'email' => sprintf('bot_%03d@%s', $existing + $sequence->index + 1, self::BOT_EMAIL_DOMAIN),
            ])
            ->create([
                'password' => Hash::make(Str::random(32)),  // random password, never used
            ]);

        $this->command->info("SimulatedUserSeeder: done. Total bots: {$count}.");
    }
}
