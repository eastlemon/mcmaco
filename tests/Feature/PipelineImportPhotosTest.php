<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Pipeline;
use App\Models\PipelineLog;
use App\Models\User;
use App\Services\PipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-end batched import: CSV + ZIP with photos/{SKU}/ folders.
 * Queue connection is "sync" in tests, so Bus batches execute inline.
 */
class PipelineImportPhotosTest extends TestCase
{
    use RefreshDatabase;

    private const JPEG_B64 = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwcJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPDs0NDT/wAALCAABAAEBAREA/8QAFAABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AmAA//9k=';

    private string $tmp;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');

        User::factory()->create(['id' => 1]);

        $this->tmp = sys_get_temp_dir() . '/import-photos-' . uniqid();
        mkdir($this->tmp, 0755, true);
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->tmp));

        parent::tearDown();
    }

    /**
     * @param array<string, string> $entries
     */
    private function buildZip(array $entries): string
    {
        $path = "{$this->tmp}/photos-" . uniqid() . '.zip';

        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE);

        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();

        return $path;
    }

    private function jpeg(int $markerBytes = 1): string
    {
        return base64_decode(self::JPEG_B64) . str_repeat('x', $markerBytes);
    }

    private function pipeline(array $config): Pipeline
    {
        return Pipeline::create([
            'name' => 'Products + Photos',
            'type' => Pipeline::TYPE_IMPORT,
            'adapter' => 'csv_products',
            'format' => 'csv',
            'config' => $config,
            'is_active' => true,
        ]);
    }

    public function test_full_import_attaches_photos_and_logs_summary(): void
    {
        $csv = "title,sku,price,stock,description,category\n"
            . "Widget One,SKU-1,100,5,First widget,Widgets\n"
            . "Widget Two,SKU-2,200,3,Second widget,Widgets\n"
            . ",SKU-3,10,1,,Widgets\n"; // broken row: no title

        Storage::disk('local')->put('pipeline-uploads/products.csv', $csv);

        $zip = $this->buildZip([
            'photos/SKU-1/1-cover.jpg' => $this->jpeg(1),
            'photos/SKU-1/2.jpg' => $this->jpeg(2),
            'photos/SKU-2/1.jpg' => $this->jpeg(3),
            'readme.txt' => 'ignored',
        ]);
        Storage::disk('local')->put('pipeline-uploads/photos.zip', file_get_contents($zip));

        $pipeline = $this->pipeline([
            'csv_file' => 'pipeline-uploads/products.csv',
            'photos_zip' => 'pipeline-uploads/photos.zip',
            'delimiter' => ',',
            'default_user_id' => 1,
            'auto_create_categories' => true,
            'photo_strategy' => 'replace',
        ]);

        $log = app(PipelineService::class)->runImport($pipeline->fresh());

        // Run summary (finalize job already ran: sync queue executes batches inline)
        $log = $log->fresh();
        $this->assertSame(PipelineLog::STATUS_SUCCESS, $log->status, $log->message);
        $this->assertSame(3, $log->processed);
        $this->assertSame(2, $log->created);
        $this->assertSame(0, $log->updated);
        $this->assertSame(1, $log->errors);
        $this->assertSame(3, $log->photos);
        $this->assertNotNull($log->details['batch_id'] ?? null);

        // The broken row has its own error log entry
        $this->assertTrue(
            $pipeline->logs()->where('status', PipelineLog::STATUS_FAILED)->where('message', 'like', '%Missing title%')->exists(),
        );

        // Products
        $one = Ad::where('sku', 'SKU-1')->first();
        $two = Ad::where('sku', 'SKU-2')->first();

        $this->assertNotNull($one);
        $this->assertNotNull($two);
        $this->assertSame(2, $one->images()->count());
        $this->assertSame(1, $two->images()->count());

        // Cover: the -cover file must be the first by sort_order
        $cover = $one->images()->orderBy('sort_order')->first();
        $this->assertSame(strlen($this->jpeg(1)), Storage::disk('public')->size($cover->path));
        $this->assertSame($cover->id, $one->images->first()->id);

        // Files actually stored on the public disk under ads/{id}/
        $this->assertSame(2, count(Storage::disk('public')->files("ads/{$one->id}")));
        $this->assertSame(1, count(Storage::disk('public')->files("ads/{$two->id}")));

        // Broken SKU-3 has no photos and no ad
        $this->assertNull(Ad::where('sku', 'SKU-3')->first());

        // Temp run dir cleaned up
        $this->assertSame([], Storage::disk('local')->directories('pipeline-runs'));
    }

    public function test_reimport_replaces_photos_when_strategy_is_replace(): void
    {
        Storage::disk('local')->put(
            'pipeline-uploads/products.csv',
            "title,sku,price,stock\nWidget One,SKU-1,100,5\n",
        );

        $zipOne = $this->buildZip([
            'photos/SKU-1/1-cover.jpg' => $this->jpeg(1),
            'photos/SKU-1/2.jpg' => $this->jpeg(2),
        ]);
        Storage::disk('local')->put('pipeline-uploads/photos.zip', file_get_contents($zipOne));

        $pipeline = $this->pipeline([
            'csv_file' => 'pipeline-uploads/products.csv',
            'photos_zip' => 'pipeline-uploads/photos.zip',
            'delimiter' => ',',
            'default_user_id' => 1,
        ]);

        app(PipelineService::class)->runImport($pipeline->fresh());

        $ad = Ad::where('sku', 'SKU-1')->firstOrFail();
        $oldPaths = $ad->images()->pluck('path')->all();
        $this->assertCount(2, $oldPaths);

        // Re-import with a different photo set
        $zipTwo = $this->buildZip([
            'photos/SKU-1/1-cover.jpg' => $this->jpeg(7),
        ]);
        Storage::disk('local')->put('pipeline-uploads/photos.zip', file_get_contents($zipTwo));

        $log = app(PipelineService::class)->runImport($pipeline->fresh());

        $ad = $ad->fresh();
        $this->assertSame(1, $ad->images()->count(), 'Replace strategy must swap the whole photo set');

        foreach ($oldPaths as $oldPath) {
            $this->assertFalse(Storage::disk('public')->exists($oldPath), 'Old photo must be deleted');
        }

        $newCover = $ad->images()->orderBy('sort_order')->first();
        $this->assertSame(strlen($this->jpeg(7)), Storage::disk('public')->size($newCover->path));

        $log = $log->fresh();
        $this->assertSame(PipelineLog::STATUS_SUCCESS, $log->status, $log->message);
        $this->assertSame(1, $log->updated);
        $this->assertSame(1, $log->photos);
    }

    public function test_reimport_keeps_photos_when_strategy_is_skip(): void
    {
        Storage::disk('local')->put(
            'pipeline-uploads/products.csv',
            "title,sku,price,stock\nWidget One,SKU-1,100,5\n",
        );

        $zipOne = $this->buildZip([
            'photos/SKU-1/1-cover.jpg' => $this->jpeg(1),
        ]);
        Storage::disk('local')->put('pipeline-uploads/photos.zip', file_get_contents($zipOne));

        $pipeline = $this->pipeline([
            'csv_file' => 'pipeline-uploads/products.csv',
            'photos_zip' => 'pipeline-uploads/photos.zip',
            'delimiter' => ',',
            'default_user_id' => 1,
            'photo_strategy' => 'skip',
        ]);

        app(PipelineService::class)->runImport($pipeline->fresh());

        $ad = Ad::where('sku', 'SKU-1')->firstOrFail();
        $oldPaths = $ad->images()->pluck('path')->all();
        $this->assertCount(1, $oldPaths);

        $zipTwo = $this->buildZip([
            'photos/SKU-1/1-cover.jpg' => $this->jpeg(9),
        ]);
        Storage::disk('local')->put('pipeline-uploads/photos.zip', file_get_contents($zipTwo));

        $log = app(PipelineService::class)->runImport($pipeline->fresh());

        $ad = $ad->fresh();
        $this->assertSame($oldPaths, $ad->images()->pluck('path')->all(), 'Skip strategy must not touch photos');

        $log = $log->fresh();
        $this->assertSame(0, $log->photos);
        $this->assertSame(1, $log->updated);
    }

    public function test_import_without_zip_updates_products_only(): void
    {
        Storage::disk('local')->put(
            'pipeline-uploads/products.csv',
            "title,sku,price,stock\nWidget,SKU-9,50,1\n",
        );

        $pipeline = $this->pipeline([
            'csv_file' => 'pipeline-uploads/products.csv',
            'delimiter' => ',',
            'default_user_id' => 1,
        ]);

        $log = app(PipelineService::class)->runImport($pipeline->fresh());

        $this->assertSame(PipelineLog::STATUS_SUCCESS, $log->status, $log->message);
        $this->assertSame(1, $log->created);
        $this->assertSame(0, $log->photos);
        $this->assertSame(0, Ad::where('sku', 'SKU-9')->firstOrFail()->images()->count());
    }

    public function test_missing_zip_marks_run_failed(): void
    {
        Storage::disk('local')->put(
            'pipeline-uploads/products.csv',
            "title,sku,price,stock\nWidget,SKU-10,50,1\n",
        );

        $pipeline = $this->pipeline([
            'csv_file' => 'pipeline-uploads/products.csv',
            'photos_zip' => 'pipeline-uploads/ghost.zip',
            'delimiter' => ',',
            'default_user_id' => 1,
        ]);

        try {
            app(PipelineService::class)->runImport($pipeline->fresh());
            $this->fail('Expected RuntimeException for missing zip');
        } catch (\RuntimeException) {
            // expected
        }

        $log = $pipeline->logs()->orderByDesc('id')->first();
        $this->assertNotNull($log);
        $this->assertSame(PipelineLog::STATUS_FAILED, $log->status);
        $this->assertStringContainsString('not found', $log->message);
    }
}