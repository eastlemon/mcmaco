<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => 'yookassa',
            'provider_payment_id' => 'yk_' . $this->faker->uuid,
            'status' => Payment::STATUS_PENDING,
            'amount' => $this->faker->numberBetween(100, 10000),
            'currency' => 'RUB',
        ];
    }
}
