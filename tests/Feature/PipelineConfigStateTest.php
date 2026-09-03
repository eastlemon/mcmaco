<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Pipelines\Pages\CreatePipeline;
use App\Filament\Admin\Resources\Pipelines\Pages\EditPipeline;
use App\Models\Pipeline;
use App\Models\User;
use App\Services\PhotoAttacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression: dynamic adapter config fields must exist in the Livewire
 * snapshot before they render. Filament 5 caches child schemas, so on mount
 * (no adapter selected) `fill()` never creates `data.config.*` state paths.
 * The rendered fields then entangle to missing properties — Livewire 4
 * throws "Livewire property ['data.config.*'] cannot be found" and Toggle /
 * FileUpload inputs stop syncing with the server.
 */
class PipelineConfigStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_selecting_import_adapter_seeds_config_state_with_defaults(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(CreatePipeline::class)
            ->set('data.type', 'import')
            ->set('data.adapter', 'csv_products')
            ->assertSet('data.config.auto_create_categories', true)
            ->assertSet('data.config.delimiter', ',')
            ->assertSet('data.config.default_user_id', 1)
            ->assertSet('data.config.photo_strategy', PhotoAttacher::STRATEGY_REPLACE)
            ->assertSet('data.config.csv_file', [])
            ->assertSet('data.config.photos_zip', [])
            ->assertSet('data.config.file_path', null);
    }

    public function test_selecting_export_adapter_seeds_config_state_with_defaults(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(CreatePipeline::class)
            ->set('data.type', 'export')
            ->set('data.adapter', 'orders_export')
            ->assertSet('data.config.days_back', 30)
            ->assertSet('data.config.delimiter', ';')
            ->assertSet('data.config.statuses', null)
            ->assertSet('data.config.csv_file', null);
    }

    public function test_switching_type_resets_config_state(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(CreatePipeline::class)
            ->set('data.type', 'import')
            ->set('data.adapter', 'csv_products')
            ->set('data.config.delimiter', ',')
            ->set('data.type', 'export')
            ->assertSet('data.config', [])
            ->assertSet('data.adapter', null);
    }

    public function test_edit_page_pads_missing_config_keys_with_defaults(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $pipeline = Pipeline::create([
            'name' => 'Legacy import',
            'type' => 'import',
            'adapter' => 'csv_products',
            'format' => 'csv',
            'is_active' => true,
            // Old record saved before the newer config keys existed.
            'config' => ['delimiter' => ';'],
        ]);

        $this->actingAs($admin);

        Livewire::test(EditPipeline::class, ['record' => $pipeline->id])
            ->assertSet('data.config.delimiter', ';') // stored value wins
            ->assertSet('data.config.auto_create_categories', true) // padded default
            ->assertSet('data.config.photo_strategy', PhotoAttacher::STRATEGY_REPLACE)
            ->assertSet('data.config.csv_file', []);
    }
}