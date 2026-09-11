<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_json_store_is_redirect(): void
    {
        $ad = Ad::factory()->create(['status' => 'active']);

        // гость с JSON-заголовками получает 401 от auth-middleware
        $this->postJson(route('favorites.store', $ad))->assertUnauthorized();
    }

    public function test_user_can_add_to_favorites_via_json(): void
    {
        $user = User::factory()->create();
        $ad = Ad::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)
            ->postJson(route('favorites.store', $ad));

        $response->assertOk()->assertJson(['favorite' => true]);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'ad_id' => $ad->id,
        ]);
    }

    public function test_user_can_remove_from_favorites_via_json(): void
    {
        $user = User::factory()->create();
        $ad = Ad::factory()->create(['status' => 'active']);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'ad_id' => $ad->id,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('favorites.destroy', $ad));

        $response->assertOk()->assertJson(['favorite' => false]);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'ad_id' => $ad->id,
        ]);
    }

    public function test_favorites_index_excludes_deleted_ads(): void
    {
        $user = User::factory()->create();
        $ad = Ad::factory()->create(['status' => 'active']);
        $deletedAd = Ad::factory()->create(['status' => 'active']);
        $deletedAd->delete(); // soft delete

        Favorite::factory()->create(['user_id' => $user->id, 'ad_id' => $ad->id]);
        Favorite::factory()->create(['user_id' => $user->id, 'ad_id' => $deletedAd->id]);

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertOk();
        $response->assertDontSee($deletedAd->title);
    }

    public function test_product_card_renders_favorite_state(): void
    {
        $user = User::factory()->create();
        $ad = Ad::factory()->create(['status' => 'active', 'title' => 'Fav Toggle Product']);
        Favorite::factory()->create(['user_id' => $user->id, 'ad_id' => $ad->id]);

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertOk();
        // залитая кнопка инициализируется с fav: true
        $this->assertTrue(str_contains($response->getContent(), 'fav: true'));
    }
}
