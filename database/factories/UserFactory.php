<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Mark this user as a simulated bot account (is_simulated=true).
     * Used by SimulatedUserSeeder and simulation generators.
     */
    public function simulated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_simulated' => true,
            // Bots get a city and phone so they look like real users in chats/orders
            'city' => fake()->randomElement([
                'Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург',
                'Казань', 'Нижний Новгород', 'Челябинск', 'Краснодар',
                'Самара', 'Томск', 'Воронеж', 'Уфа',
            ]),
            'phone' => '+7' . fake()->numerify('9## ### ## ##'),
            // Avatar URL from DiceBear (free, no API key required)
            'avatar' => sprintf(
                'https://api.dicebear.com/9.x/%s/svg?seed=%s',
                config('simulation.avatar_style', 'avataaars'),
                urlencode($attributes['name'] ?? fake()->name())
            ),
        ]);
    }
}
