<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Pipeline;
use App\Models\User;
use App\Services\PipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Photos from CSV/ZIP import are re-encoded to optimized WebP (≤1600px):
 * oversized raw files are accepted and stored as small webp files.
 */
class ImportImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_oversized_photo_is_attached_as_optimized_webp(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        User::factory()->create(['id' => 1]);

        // Raw file above the old 5 MB cap: real JPEG + 6 MB of trailing junk.
        $im = imagecreatetruecolor(3000, 2000);
        imagefilledrectangle($im, 0, 0, 3000, 2000, 0x3366AA);
        ob_start();
        imagejpeg($im, null, 30);
        $raw = (string) ob_get_clean();
        $raw .= str_repeat('x', 6 * 1024 * 1024);
        $this->assertGreaterThan(5242880, strlen($raw));

        $zipPath = sys_get_temp_dir() . '/opt-' . uniqid() . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFromString('photos/SKU-1/1-cover.png', $raw);
        $zip->close();

        Storage::disk('local')->put('pipeline-uploads/photos.zip', file_get_contents($zipPath));
        Storage::disk('local')->put('pipeline-uploads/products.csv',
            "title,sku,price,stock,description,category\nWidget One,SKU-1,100,5,First,Widgets\n");

        $pipeline = Pipeline::create([
            'name' => 'Optimization test',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'config' => [
                'csv_file' => 'pipeline-uploads/products.csv',
                'photos_zip' => 'pipeline-uploads/photos.zip',
                'delimiter' => ',',
                'default_user_id' => 1,
                'auto_create_categories' => true,
            ],
            'is_active' => true,
        ]);

        app(PipelineService::class)->runImport($pipeline->fresh());

        $ad = Ad::where('sku', 'SKU-1')->firstOrFail();
        $image = $ad->images->first();

        $this->assertNotNull($image, 'photo should be attached');
        $this->assertSame('webp', pathinfo($image->path, PATHINFO_EXTENSION));
        $this->assertLessThan(
            1024 * 1024,
            Storage::disk('public')->size($image->path),
            'optimized image should be well under 1 MB',
        );
    }
}
