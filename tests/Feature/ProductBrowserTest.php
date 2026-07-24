<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBrowserTest extends TestCase
{
    use RefreshDatabase;

    private function createAd(array $overrides = []): Ad
    {
        return Ad::factory()->create(array_merge([
            'title' => 'Test Product',
            'price' => 1000,
            'stock' => 5,
            'status' => 'active',
            'condition' => 'new',
            'city' => 'Москва',
            'category_id' => null,
            'user_id' => User::factory(),
        ], $overrides));
    }

    public function test_browser_renders_with_no_filters(): void
    {
        $this->createAd();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeLivewire('product-browser');
    }

    public function test_search_by_title(): void
    {
        $this->createAd(['title' => 'iPhone 15 Pro']);
        $this->createAd(['title' => 'Samsung Galaxy']);

        $response = $this->get('/?q=iPhone');

        $response->assertOk();
        $response->assertSee('iPhone 15 Pro');
        $response->assertDontSee('Samsung Galaxy');
    }

    public function test_filter_by_category_including_children(): void
    {
        $parent = Category::create(['name' => 'Электроника', 'slug' => 'electronics']);
        $child = Category::create(['name' => 'Телефоны', 'slug' => 'phones', 'parent_id' => $parent->id]);
        $other = Category::create(['name' => 'Одежда', 'slug' => 'clothes']);

        $this->createAd(['title' => 'Parent Product', 'category_id' => $parent->id]);
        $this->createAd(['title' => 'Child Product', 'category_id' => $child->id]);
        $this->createAd(['title' => 'Other Product', 'category_id' => $other->id]);

        $response = $this->get("/?category_id={$parent->id}");

        $response->assertOk();
        $response->assertSee('Parent Product');
        $response->assertSee('Child Product');
        $response->assertDontSee('Other Product');
    }

    public function test_filter_by_price_range(): void
    {
        $this->createAd(['title' => 'Cheap Item', 'price' => 500]);
        $this->createAd(['title' => 'Mid Item', 'price' => 2000]);
        $this->createAd(['title' => 'Expensive Item', 'price' => 50000]);

        $response = $this->get('/?minPrice=1000&maxPrice=10000');

        $response->assertOk();
        $response->assertSee('Mid Item');
        $response->assertDontSee('Cheap Item');
        $response->assertDontSee('Expensive Item');
    }

    public function test_filter_by_condition(): void
    {
        $this->createAd(['title' => 'Brand New', 'condition' => 'new']);
        $this->createAd(['title' => 'Second Hand', 'condition' => 'used']);

        $response = $this->get('/?condition=used');

        $response->assertOk();
        $response->assertSee('Second Hand');
        $response->assertDontSee('Brand New');
    }

    public function test_filter_by_city(): void
    {
        $this->createAd(['title' => 'Moscow Item', 'city' => 'Москва']);
        $this->createAd(['title' => 'SPB Item', 'city' => 'Санкт-Петербург']);

        $response = $this->get('/?city=Москва');

        $response->assertOk();
        $response->assertSee('Moscow Item');
        $response->assertDontSee('SPB Item');
    }

    public function test_sort_by_price_ascending(): void
    {
        $this->createAd(['title' => 'Expensive', 'price' => 9999]);
        $this->createAd(['title' => 'Cheap', 'price' => 100]);

        $response = $this->get('/?sort=price_asc');

        $response->assertOk();
        $html = $response->getContent();
        $cheapPos = strpos($html, 'Cheap');
        $expensivePos = strpos($html, 'Expensive');
        $this->assertNotFalse($cheapPos);
        $this->assertNotFalse($expensivePos);
        $this->assertLessThan($expensivePos, $cheapPos);
    }

    public function test_in_stock_only_filter(): void
    {
        $this->createAd(['title' => 'Available', 'stock' => 10]);
        $this->createAd(['title' => 'Sold Out', 'stock' => 0]);

        // Default view shows only in-stock
        $response = $this->get('/');
        $response->assertSee('Available');
        $response->assertDontSee('Sold Out');
    }

    public function test_featured_only_filter(): void
    {
        $this->createAd(['title' => 'Regular', 'is_featured' => false]);
        $this->createAd(['title' => 'Star', 'is_featured' => true]);

        $response = $this->get('/?featuredOnly=1');

        $response->assertOk();
        $response->assertSee('Star');
        $response->assertDontSee('Regular');
    }

    public function test_inactive_ads_not_shown(): void
    {
        $this->createAd(['title' => 'Active', 'status' => 'active']);
        $this->createAd(['title' => 'Draft', 'status' => 'draft']);

        $response = $this->get('/');

        $response->assertSee('Active');
        $response->assertDontSee('Draft');
    }

    public function test_combined_filters(): void
    {
        $cat = Category::create(['name' => 'Tech', 'slug' => 'tech']);

        $this->createAd([
            'title' => 'Perfect Match',
            'price' => 3000,
            'condition' => 'new',
            'category_id' => $cat->id,
        ]);
        $this->createAd([
            'title' => 'Wrong Price',
            'price' => 99999,
            'condition' => 'new',
            'category_id' => $cat->id,
        ]);
        $this->createAd([
            'title' => 'Wrong Condition',
            'price' => 3000,
            'condition' => 'used',
            'category_id' => $cat->id,
        ]);

        $response = $this->get("/?category_id={$cat->id}&condition=new&maxPrice=10000");

        $response->assertOk();
        $response->assertSee('Perfect Match');
        $response->assertDontSee('Wrong Price');
        $response->assertDontSee('Wrong Condition');
    }
}
