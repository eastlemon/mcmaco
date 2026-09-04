<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Pipelines\Pages\CreatePipeline;
use App\Models\Pipeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression: Filament feeds acceptedFileTypes() into the `mimetypes:`
 * validation rule verbatim. With bare extensions (['.csv']) the rule is
 * unsatisfiable — Symfony guesses the MIME from file content (text/csv,
 * text/plain) and never equals ".csv" — so every CSV upload failed
 * validation on create, the record was not saved and the user's file
 * silently disappeared. Accepted lists must include real MIME types;
 * makeFileField() now expands known extensions automatically.
 */
class PipelineCsvUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_upload_passes_validation_and_is_stored(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Storage::fake('local');

        $csv = "title,sku,price,stock\nProbe Pencil,P-001,100,2\n";

        Livewire::test(CreatePipeline::class)
            ->set('data.name', 'Csv upload probe')
            ->set('data.type', 'import')
            ->set('data.adapter', 'csv_products')
            ->set('data.config.csv_file', UploadedFile::fake()->createWithContent('products.csv', $csv))
            ->call('create')
            ->assertHasNoErrors();

        $pipeline = Pipeline::where('name', 'Csv upload probe')->first();
        $this->assertNotNull($pipeline, 'Pipeline record was not created');

        $storedPath = $pipeline->config['csv_file'] ?? null;
        $this->assertIsString($storedPath, 'csv_file config was not persisted');
        $this->assertStringStartsWith('pipeline-uploads/', $storedPath);

        Storage::disk('local')->assertExists($storedPath);
        $this->assertSame($csv, Storage::disk('local')->get($storedPath));
    }

    public function test_zip_photo_archive_upload_passes_validation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Storage::fake('local');

        // Livewire's testable upload() reads `$file->name`, which only exists
        // on fake files — so build real ZIP bytes and wrap them in a fake.
        $zipFile = tempnam(sys_get_temp_dir(), 'probe') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE);
        $zip->addFromString('photos/P-001/1-cover.jpg', 'not-really-a-jpeg');
        $zip->close();
        $zipBytes = file_get_contents($zipFile);
        unlink($zipFile);

        Livewire::test(CreatePipeline::class)
            ->set('data.name', 'Zip upload probe')
            ->set('data.type', 'import')
            ->set('data.adapter', 'csv_products')
            ->set('data.config.photos_zip', UploadedFile::fake()->createWithContent('photos.zip', $zipBytes))
            ->call('create')
            ->assertHasNoErrors();

        $pipeline = Pipeline::where('name', 'Zip upload probe')->first();
        $this->assertNotNull($pipeline, 'Pipeline record was not created');

        $storedPath = $pipeline->config['photos_zip'] ?? null;
        $this->assertIsString($storedPath, 'photos_zip config was not persisted');

        Storage::disk('local')->assertExists($storedPath);
    }
}