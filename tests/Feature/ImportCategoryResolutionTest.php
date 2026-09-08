<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Category;
use App\Models\Pipeline;
use App\Models\PipelineLog;
use App\Models\User;
use App\Services\PipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression: rows sharing a category (with inconsistent whitespace, e.g.
 * "Bulk Cat" vs " Bulk Cat") and concurrent slug collisions must not lose
 * rows — all rows resolve to the same auto-created category.
 */
class ImportCategoryResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_rows_with_sloppy_whitespace_share_one_category(): void
    {
        Storage::fake('local');
        User::factory()->create(['id' => 1]);

        $csv = "title,sku,price,stock,description,category\n"
            . "Widget One,SKU-1,100,5,First, Bulk Cat\n"   // leading space
            . "Widget Two,SKU-2,200,3,Second,Bulk Cat\n";  // clean

        Storage::disk('local')->put('pipeline-uploads/products.csv', $csv);

        $pipeline = Pipeline::create([
            'name' => 'Category test',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'config' => [
                'csv_file' => 'pipeline-uploads/products.csv',
                'delimiter' => ',',
                'default_user_id' => 1,
                'auto_create_categories' => true,
            ],
            'is_active' => true,
        ]);

        $log = app(PipelineService::class)->runImport($pipeline->fresh());

        $this->assertSame(PipelineLog::STATUS_SUCCESS, $log->refresh()->status);

        $this->assertSame(1, Category::where('slug', 'bulk-cat')->count());
        $category = Category::where('slug', 'bulk-cat')->first();

        $this->assertSame(2, Ad::whereIn('sku', ['SKU-1', 'SKU-2'])->count());
        $this->assertSame(
            [$category->id, $category->id],
            Ad::whereIn('sku', ['SKU-1', 'SKU-2'])->orderBy('sku')->pluck('category_id')->all(),
        );
    }

    public function test_slug_collision_reuses_existing_category(): void
    {
        Storage::fake('local');
        User::factory()->create(['id' => 1]);

        // Pre-existing category with the same slug but a different name —
        // insert will collide on the unique slug, import must reuse it.
        Category::create(['name' => 'Existing Category', 'slug' => 'bulk-cat']);

        $csv = "title,sku,price,stock,description,category\n"
            . "Widget One,SKU-1,100,5,First,Bulk Cat\n";

        Storage::disk('local')->put('pipeline-uploads/products.csv', $csv);

        $pipeline = Pipeline::create([
            'name' => 'Collision test',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'config' => [
                'csv_file' => 'pipeline-uploads/products.csv',
                'delimiter' => ',',
                'default_user_id' => 1,
                'auto_create_categories' => true,
            ],
            'is_active' => true,
        ]);

        $log = app(PipelineService::class)->runImport($pipeline->fresh());

        $this->assertSame(PipelineLog::STATUS_SUCCESS, $log->refresh()->status);
        $existing = Category::where('slug', 'bulk-cat')->first();
        $this->assertSame(1, Ad::where('sku', 'SKU-1')->where('category_id', $existing->id)->count());
    }
}
