<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'order_number' => 'MC-' . date('Ymd') . '-' . strtoupper($this->faker->bothify('####')),
            'status' => Order::STATUS_NEW,
            'customer_name' => $this->faker->name,
            'customer_phone' => '+7' . $this->faker->numerify('##########'),
            'customer_email' => $this->faker->safeEmail,
            'delivery_method' => 'pickup',
            'delivery_cost' => 0,
            'items_total' => $this->faker->numberBetween(500, 5000),
            'total' => $this->faker->numberBetween(500, 5000),
        ];
    }
}
