<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: the pipelines list page must expose the "create" header action
 * (it went missing while every other resource declares it explicitly).
 */
class AdminPipelinesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_create_button_on_pipelines_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/pipelines')
            ->assertOk()
            ->assertSee('pipelines/create');
    }

    public function test_non_admin_cannot_access_pipelines_list(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/pipelines')->assertForbidden();
    }
}