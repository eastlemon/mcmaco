<?php

namespace Tests\Feature;

use App\Models\Ad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAddTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_item_to_cart_via_post(): void
    {
        $ad = Ad::factory()->create([
            'status' => 'active',
            'stock' => 10,
            'price' => 1500,
        ]);

        $response = $this->postJson(route('cart.add'), [
            'ad_id' => $ad->id,
            'qty' => 2,
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertEquals(2, $response->json('count'));
        $this->assertEquals(3000, $response->json('total'));
    }

    public function test_add_to_cart_defaults_qty_to_1(): void
    {
        $ad = Ad::factory()->create([
            'status' => 'active',
            'stock' => 5,
            'price' => 500,
        ]);

        $response = $this->postJson(route('cart.add'), [
            'ad_id' => $ad->id,
        ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('count'));
    }

    public function test_add_to_cart_validates_ad_id(): void
    {
        $response = $this->postJson(route('cart.add'), [
            'ad_id' => 99999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('ad_id');
    }

    public function test_add_to_cart_validates_qty(): void
    {
        $ad = Ad::factory()->create([
            'status' => 'active',
            'stock' => 5,
        ]);

        $response = $this->postJson(route('cart.add'), [
            'ad_id' => $ad->id,
            'qty' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('qty');
    }

    public function test_non_json_request_redirects_back(): void
    {
        $ad = Ad::factory()->create([
            'status' => 'active',
            'stock' => 5,
        ]);

        $response = $this->post(route('cart.add'), [
            'ad_id' => $ad->id,
            'qty' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('added', true);
    }
}