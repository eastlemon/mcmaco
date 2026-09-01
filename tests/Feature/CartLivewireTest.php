<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Live Livewire protocol tests: real HTTP requests against /livewire/update,
 * the same round-trip the browser JS performs for wire:click actions.
 */
class CartLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Ad $ad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->ad = Ad::factory()->create([
            'status' => 'active',
            'stock' => 10,
            'price' => 500,
        ]);
    }

    /** The user's cart survives HTTP requests via the user_id binding. */
    private function cartQty(): int
    {
        return (int) Cart::where('user_id', $this->user->id)
            ->first()
            ?->items()
            ->where('ad_id', $this->ad->id)
            ->value('qty');
    }

    private function seedCart(int $qty): void
    {
        $this->postJson(route('cart.add'), [
            'ad_id' => $this->ad->id,
            'qty' => $qty,
        ])->assertOk();
    }

    /**
     * Extract the wire:snapshot JSON for a given component from page HTML.
     */
    private function snapshotFor(string $html, string $componentName): string
    {
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);

        foreach ($matches[1] as $encoded) {
            $json = htmlspecialchars_decode($encoded, ENT_QUOTES);
            $snapshot = json_decode($json, true);

            if (($snapshot['memo']['name'] ?? null) === $componentName) {
                return $json;
            }
        }

        $this->fail("No wire:snapshot found for component [{$componentName}].");
    }

    private function livewireUpdate(string $snapshot, string $method, array $params)
    {
        // Real Livewire JS sends JSON body + X-Livewire header;
        // RequireLivewireHeaders middleware 404s anything else.
        return $this->postJson(app('livewire')->getUpdateUri(), [
            'components' => [
                [
                    'snapshot' => $snapshot,
                    'calls' => [
                        [
                            'method' => $method,
                            'params' => $params,
                            'path' => '',
                        ],
                    ],
                    'updates' => [],
                ],
            ],
        ], ['X-Livewire' => 'true']);
    }

    public function test_cart_page_increments_item_qty_via_live_protocol(): void
    {
        $this->actingAs($this->user);
        $this->seedCart(2);

        $page = $this->get(route('cart'))->assertOk();
        $snapshot = $this->snapshotFor($page->getContent(), 'cart-page');

        $this->livewireUpdate($snapshot, 'incrementQty', [$this->ad->id])->assertOk();

        $this->assertEquals(3, $this->cartQty());
    }

    public function test_cart_page_decrements_item_qty_via_live_protocol(): void
    {
        $this->actingAs($this->user);
        $this->seedCart(2);

        $page = $this->get(route('cart'))->assertOk();
        $snapshot = $this->snapshotFor($page->getContent(), 'cart-page');

        $this->livewireUpdate($snapshot, 'decrementQty', [$this->ad->id])->assertOk();

        $this->assertEquals(1, $this->cartQty());
    }

    public function test_cart_page_decrement_to_zero_removes_item(): void
    {
        $this->actingAs($this->user);
        $this->seedCart(1);

        $page = $this->get(route('cart'))->assertOk();
        $snapshot = $this->snapshotFor($page->getContent(), 'cart-page');

        $this->livewireUpdate($snapshot, 'decrementQty', [$this->ad->id])->assertOk();

        $this->assertEquals(0, $this->cartQty());
    }

    public function test_cart_page_increment_is_capped_at_stock(): void
    {
        $this->ad->update(['stock' => 3]);
        $this->actingAs($this->user);
        $this->seedCart(3);

        $page = $this->get(route('cart'))->assertOk();
        $snapshot = $this->snapshotFor($page->getContent(), 'cart-page');

        $this->livewireUpdate($snapshot, 'incrementQty', [$this->ad->id])->assertOk();

        $this->assertEquals(3, $this->cartQty());
    }

    public function test_cart_page_removes_item_via_live_protocol(): void
    {
        $this->actingAs($this->user);
        $this->seedCart(2);

        $page = $this->get(route('cart'))->assertOk();
        $snapshot = $this->snapshotFor($page->getContent(), 'cart-page');

        $this->livewireUpdate($snapshot, 'removeItem', [$this->ad->id])->assertOk();

        $this->assertEquals(0, $this->cartQty());
    }

    public function test_cart_page_renders_items_and_total(): void
    {
        $this->actingAs($this->user);
        $this->seedCart(2);

        $this->get(route('cart'))
            ->assertOk()
            ->assertSee($this->ad->title)
            ->assertSee('1 000');
    }

    public function test_navbar_cart_badge_shows_count(): void
    {
        $this->actingAs($this->user);
        $this->seedCart(3);

        $page = $this->get(route('ads.index'))->assertOk();
        $html = $page->getContent();

        // Badge span next to the cart icon must be rendered with the count
        $this->assertStringContainsString('min-w-5 h-5 px-1 bg-amber-600', $html);
        $this->assertMatchesRegularExpression(
            '/bg-amber-600[^>]*>\s*3\s*<\/span>/',
            $html,
            'Navbar badge should contain the item count.',
        );
    }

    public function test_cart_dropdown_refreshes_stats_via_live_protocol(): void
    {
        $this->actingAs($this->user);
        $this->seedCart(3);

        $page = $this->get(route('ads.index'))->assertOk();
        $snapshot = $this->snapshotFor($page->getContent(), 'cart-dropdown');

        $response = $this->livewireUpdate($snapshot, 'refreshStats', [])->assertOk();

        $updated = json_decode($response->json('components.0.snapshot'), true);

        $this->assertEquals(3, $updated['data']['itemsCount']);
        $this->assertEquals(1500, $updated['data']['total']);
    }
}