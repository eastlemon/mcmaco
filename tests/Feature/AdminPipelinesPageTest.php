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

    public function test_create_page_renders_form_fields(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Regression for the "blank create page": Filament 5 pulls the form
        // from PipelineResource::form(); a page-level getFormSchema() is ignored.
        $this->actingAs($admin)
            ->get('/admin/pipelines/create')
            ->assertOk()
            ->assertSee(__('filament.pipelines.fields.name'))
            ->assertSee(__('filament.pipelines.sections.main'));
    }

    public function test_edit_page_renders_form_fields(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $pipeline = \App\Models\Pipeline::create([
            'name' => 'Test import',
            'type' => 'import',
            'adapter' => 'csv_products',
            'format' => 'csv',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get("/admin/pipelines/{$pipeline->id}/edit")
            ->assertOk()
            ->assertSee(__('filament.pipelines.fields.name'));
    }

    public function test_non_admin_cannot_access_pipelines_list(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/pipelines')->assertForbidden();
    }
}