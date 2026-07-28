<?php

namespace Database\Factories;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ad>
 */
class AdFactory extends Factory
{
    protected $model = Ad::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(5),
            'description' => $this->faker->paragraphs(3, true),
            'price' => $this->faker->numberBetween(0, 100000),
            'city' => $this->faker->city(),
            'condition' => $this->faker->randomElement(['new', 'used']),
            'status' => 'pending',
            'views' => $this->faker->numberBetween(0, 300),
        ];
    }
}
