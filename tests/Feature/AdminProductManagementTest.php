<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\AdImage;
use Database\Factories\AdFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_ad_can_be_created_without_owner(): void
    {
        $ad = Ad::query()->create([
            'user_id' => null,
            'title' => 'Кофеварка Delonghi',
            'slug' => 'kofevarka-delonghi-abc123',
            'description' => 'Автоматическая кофеварка, новая.',
            'price' => 24990,
            'stock' => 3,
            'city' => 'Москва',
            'condition' => 'new',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('ads', [
            'id' => $ad->id,
            'user_id' => null,
            'title' => 'Кофеварка Delonghi',
        ]);

        $this->assertNull($ad->user);
    }

    public function test_sync_images_creates_rows_in_given_order(): void
    {
        Storage::fake('public');
        $ad = Ad::factory()->create();

        $paths = [
            'ads/draft/11111111-1111-1111-1111-111111111111.jpg',
            'ads/draft/22222222-2222-2222-2222-222222222222.jpg',
        ];

        foreach ($paths as $path) {
            Storage::disk('public')->put($path, 'content');
        }

        $ad->syncImages($paths);

        $ad->refresh();
        $this->assertCount(2, $ad->images);

        // Порядок массива = sort_order
        $ad->images->each(function (AdImage $image, int $index): void {
            $this->assertSame($index, $image->sort_order);
            $this->assertStringStartsWith("ads/{$image->ad_id}/", $image->path);
        });

        // Оба файла переехали из ads/draft/ в ads/{id}/
        $this->assertCount(0, Storage::disk('public')->files('ads/draft'));
        $this->assertCount(2, Storage::disk('public')->files("ads/{$ad->id}"));
    }

    public function test_sync_images_removes_deleted_rows_and_files(): void
    {
        Storage::fake('public');
        $ad = Ad::factory()->create();

        $keep = "ads/{$ad->id}/aaaaaaaa-0000-0000-0000-000000000001.jpg";
        $drop = "ads/{$ad->id}/aaaaaaaa-0000-0000-0000-000000000002.jpg";

        Storage::disk('public')->put($keep, 'keep');
        Storage::disk('public')->put($drop, 'drop');

        $ad->images()->createMany([
            ['path' => $keep, 'sort_order' => 0],
            ['path' => $drop, 'sort_order' => 1],
        ]);

        $ad->refresh();
        $ad->syncImages([$keep]);

        $this->assertDatabaseHas('ad_images', ['ad_id' => $ad->id, 'path' => $keep]);
        $this->assertDatabaseMissing('ad_images', ['ad_id' => $ad->id, 'path' => $drop]);

        Storage::disk('public')->assertExists($keep);
        Storage::disk('public')->assertMissing($drop);

        $this->assertSame(
            [$keep],
            $ad->images->pluck('path')->all(),
        );
    }

    public function test_sync_images_reorders_existing(): void
    {
        Storage::fake('public');
        $ad = Ad::factory()->create();

        $first = "ads/{$ad->id}/bbbbbbbb-0000-0000-0000-000000000001.jpg";
        $second = "ads/{$ad->id}/bbbbbbbb-0000-0000-0000-000000000002.jpg";

        $ad->images()->createMany([
            ['path' => $first, 'sort_order' => 0],
            ['path' => $second, 'sort_order' => 1],
        ]);

        $ad->refresh();
        $ad->syncImages([$second, $first]);

        $ad->refresh();
        $this->assertSame(
            [$second, $first],
            $ad->images->pluck('path')->all(),
        );
    }

    public function test_image_url_accessor_points_to_public_disk(): void
    {
        Storage::fake('public');
        $ad = Ad::factory()->create();

        $image = $ad->images()->create([
            'path' => "ads/{$ad->id}/cccccccc-0000-0000-0000-000000000001.jpg",
            'sort_order' => 0,
        ]);

        $this->assertStringContainsString('/storage/', $image->url);
    }

    public function test_filament_ads_pages_render(): void
    {
        $admin = \App\Models\User::factory()->create(['is_admin' => true]);
        $ad = Ad::factory()->create(['user_id' => null, 'status' => 'active']);

        $response = $this->actingAs($admin)->get('/admin/ads');
        $response->assertOk()->assertSee('Товары');

        $this->actingAs($admin)->get('/admin/ads/create')->assertOk();

        $this->actingAs($admin)->get("/admin/ads/{$ad->id}/edit")->assertOk();
    }

    public function test_ad_can_be_created_without_city(): void
    {
        // Регрессия: ads.city был NOT NULL (наследие доски), и создание товара
        // из админки без города падало с SQLSTATE 1048 → 500 на «Сохранить».
        $ad = Ad::query()->create([
            'title' => 'Товар без города',
            'slug' => 'tovar-bez-goroda-' . uniqid(),
            'description' => 'Описание товара без города.',
            'price' => 1500,
            'stock' => 5,
            'city' => null,
            'category_id' => \App\Models\Category::create(['name' => 'Тест', 'slug' => 'test-' . uniqid()])->id,
            'condition' => 'new',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'city' => null]);
    }

    public function test_filament_create_ad_without_city(): void
    {
        // Полный путь через Filament-страницу: город не указан в форме.
        $admin = \App\Models\User::factory()->create(['is_admin' => true]);

        \Livewire::test(\App\Filament\Admin\Resources\Ads\Pages\CreateAd::class)
            ->fillForm([
                'title' => 'Товар без города',
                'slug' => 'tovar-bez-goroda-' . uniqid(),
                'description' => 'Описание товара без города.',
                'price' => 1500,
                'stock' => 5,
                'category_id' => \App\Models\Category::create(['name' => 'Тест', 'slug' => 'test-' . uniqid()])->id,
                'condition' => 'new',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ads', [
            'title' => 'Товар без города',
            'city' => null,
        ]);
    }

    public function test_regular_user_cannot_access_admin_panel(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/ads')->assertForbidden();
    }
}