<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_returns_200(): void
    {
        $category = Category::create([
            'name' => 'Электроника',
            'slug' => 'elektronika',
        ]);

        $response = $this->get(route('categories.show', $category->slug));

        $response->assertOk();
        $response->assertSee('Электроника');
    }

    public function test_category_page_shows_products(): void
    {
        $category = Category::create([
            'name' => 'Книги',
            'slug' => 'knigi',
        ]);

        $ad = Ad::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
            'stock' => 3,
            'title' => 'Уникальная книга теста',
        ]);

        $response = $this->get(route('categories.show', $category->slug));

        $response->assertOk();
        $response->assertSee('Уникальная книга теста');
    }

    public function test_category_page_includes_subcategory_products(): void
    {
        $parent = Category::create([
            'name' => 'Дом',
            'slug' => 'dom',
        ]);

        $child = Category::create([
            'name' => 'Мебель',
            'slug' => 'mebel',
            'parent_id' => $parent->id,
        ]);

        $ad = Ad::factory()->create([
            'category_id' => $child->id,
            'status' => 'active',
            'stock' => 1,
            'title' => 'Тумба из теста',
        ]);

        $response = $this->get(route('categories.show', $parent->slug));

        $response->assertOk();
        $response->assertSee('Тумба из теста');
    }

    public function test_category_page_404_for_unknown_slug(): void
    {
        $response = $this->get('/category/neexistuyushaya');

        $response->assertNotFound();
    }

    public function test_category_page_search_by_sku(): void
    {
        $category = Category::create([
            'name' => 'Инструменты',
            'slug' => 'instrumenty',
        ]);

        Ad::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
            'stock' => 1,
            'sku' => 'TEST-SKU-123',
            'title' => 'Молоток',
        ]);

        $response = $this->get(route('categories.show', ['slug' => $category->slug, 'q' => 'TEST-SKU-123']));

        $response->assertOk();
        $response->assertSee('Молоток');
    }

    public function test_category_page_has_breadcrumb_schema(): void
    {
        $category = Category::create([
            'name' => 'Тестовая категория',
            'slug' => 'testovaya-kategoriya',
        ]);

        $response = $this->get(route('categories.show', $category->slug));

        $response->assertOk();
        $response->assertSee('BreadcrumbList', false);
    }

    public function test_category_page_sort_by_price(): void
    {
        $category = Category::create([
            'name' => 'Сортировка',
            'slug' => 'sortirovka',
        ]);

        Ad::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
            'stock' => 1,
            'price' => 100,
            'title' => 'Дешёвый товар',
        ]);

        Ad::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
            'stock' => 1,
            'price' => 9999,
            'title' => 'Дорогой товар',
        ]);

        $response = $this->get(route('categories.show', ['slug' => $category->slug, 'sort' => 'price_asc']));

        $response->assertOk();
        $content = $response->content();
        $posCheap = strpos($content, 'Дешёвый товар');
        $posExpensive = strpos($content, 'Дорогой товар');
        $this->assertNotFalse($posCheap);
        $this->assertNotFalse($posExpensive);
        $this->assertLessThan($posExpensive, $posCheap);
    }
}