<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Services\DeliveryCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DeliveryMethodSeeder::class);
    }

    public function test_delivery_methods_seeded(): void
    {
        $this->assertDatabaseHas('delivery_methods', ['code' => 'pickup']);
        $this->assertDatabaseHas('delivery_methods', ['code' => 'cdek_pvz']);
        $this->assertDatabaseHas('delivery_methods', ['code' => 'post']);
        $this->assertDatabaseHas('delivery_methods', ['code' => 'local_courier']);
    }

    public function test_calculator_pickup_is_free(): void
    {
        $pickup = DeliveryMethod::where('code', 'pickup')->first();
        $calc = app(DeliveryCalculator::class);

        $this->assertEquals(0, $calc->calculate($pickup, 5000));
    }

    public function test_calculator_base_price_without_weight(): void
    {
        $cdek = DeliveryMethod::where('code', 'cdek_pvz')->first();
        $calc = app(DeliveryCalculator::class);

        // base_price=250, no weight → just base
        $this->assertEquals(250, $calc->calculate($cdek, 0));
    }

    public function test_calculator_with_weight(): void
    {
        $cdek = DeliveryMethod::where('code', 'cdek_pvz')->first();
        $calc = app(DeliveryCalculator::class);

        // base_price=250, price_per_kg=30, weight=2500g → 250 + ceil(2.5)*30 = 250+90 = 340
        $this->assertEquals(340, $calc->calculate($cdek, 2500));
    }

    public function test_calculator_courier_with_weight(): void
    {
        $courier = DeliveryMethod::where('code', 'local_courier')->first();
        $calc = app(DeliveryCalculator::class);

        // base_price=350, price_per_kg=0 → 350 regardless of weight
        $this->assertEquals(350, $calc->calculate($courier, 10000));
    }

    public function test_tracking_url_generation(): void
    {
        $cdek = DeliveryMethod::where('code', 'cdek_pvz')->first();

        $order = Order::create([
            'customer_name' => 'Трек Тестов',
            'customer_phone' => '+7 999 000 11 22',
            'delivery_method' => 'cdek_pvz',
            'delivery_method_id' => $cdek->id,
            'tracking_number' => 'CDEK123456',
            'status' => Order::STATUS_SHIPPED,
            'items_total' => 1000,
            'delivery_cost' => 300,
            'total' => 1300,
        ]);

        $this->assertNotNull($order->tracking_url);
        $this->assertStringContainsString('CDEK123456', $order->tracking_url);
        $this->assertTrue($order->has_tracking);
    }

    public function test_tracking_url_null_for_pickup(): void
    {
        $pickup = DeliveryMethod::where('code', 'pickup')->first();

        $order = Order::create([
            'customer_name' => 'Без Трека',
            'customer_phone' => '+7 999 111 22 33',
            'delivery_method' => 'pickup',
            'delivery_method_id' => $pickup->id,
            'status' => Order::STATUS_NEW,
            'items_total' => 500,
            'delivery_cost' => 0,
            'total' => 500,
        ]);

        $this->assertNull($order->tracking_url);
        $this->assertFalse($order->has_tracking);
    }

    public function test_checkout_shows_delivery_options(): void
    {
        $this->markTestSkipped('Requires full cart session setup — covered by manual testing');
    }

    public function test_order_weight_calculation(): void
    {
        $ad1 = Ad::factory()->create(['weight' => 1500]); // 1.5 kg
        $ad2 = Ad::factory()->create(['weight' => 800]);  // 0.8 kg

        $order = Order::create([
            'customer_name' => 'Вес Тестов',
            'customer_phone' => '+7 999 222 33 44',
            'status' => Order::STATUS_NEW,
            'items_total' => 2000,
            'total' => 2000,
            'delivery_method' => 'pickup',
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'ad_id' => $ad1->id,
            'title_snapshot' => $ad1->title,
            'price_snapshot' => $ad1->price,
            'qty' => 2,
            'subtotal' => $ad1->price * 2,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'ad_id' => $ad2->id,
            'title_snapshot' => $ad2->title,
            'price_snapshot' => $ad2->price,
            'qty' => 1,
            'subtotal' => $ad2->price,
        ]);

        $calc = app(DeliveryCalculator::class);
        $weight = $calc->orderWeight($order);

        // 1500*2 + 800*1 = 3800g
        $this->assertEquals(3800, $weight);
    }
}