<?php

namespace Tests\Unit;

use App\Models\Ad;
use App\Services\PhotoAttacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoAttacherTest extends TestCase
{
    use RefreshDatabase;

    /** Minimal valid 1x1 JPEG. */
    private const JPEG_B64 = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwcJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPDs0NDT/wAALCAABAAEBAREA/8QAFAABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AmAA//9k=';

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->dir = sys_get_temp_dir() . '/photo-attacher-' . uniqid();
        mkdir($this->dir, 0755, true);
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->dir));

        parent::tearDown();
    }

    /**
     * Write a tiny valid JPEG with a per-file trailing marker so
     * stored images can be told apart by their file size.
     */
    private function jpeg(string $name, int $markerBytes = 1): string
    {
        $content = base64_decode(self::JPEG_B64) . str_repeat('x', $markerBytes);
        $path = rtrim($this->dir, '/') . '/' . $name;
        file_put_contents($path, $content);

        return $path;
    }

    public function test_cover_marker_wins_first_position_and_numbering_orders_the_rest(): void
    {
        $cover = $this->jpeg('1-cover.jpg', 1);
        $second = $this->jpeg('2.jpg', 2);
        $third = $this->jpeg('3.jpg', 3);
        $fourth = $this->jpeg('10.jpg', 4); // "10" must sort after "3" numerically, not lexicographically

        $ad = Ad::factory()->create(['sku' => 'A-1']);
        $result = app(PhotoAttacher::class)->attach($ad, $this->dir);

        $this->assertSame(4, $result['attached']);
        $this->assertSame([], $result['skipped']);

        $images = $ad->images()->orderBy('sort_order')->get();

        $this->assertSame(4, $images->count());
        $this->assertSame(filesize($cover), Storage::disk('public')->size($images[0]->path));
        $this->assertSame(filesize($second), Storage::disk('public')->size($images[1]->path));
        $this->assertSame(filesize($third), Storage::disk('public')->size($images[2]->path));
        $this->assertSame(filesize($fourth), Storage::disk('public')->size($images[3]->path));

        $this->assertSame($images[0]->id, $ad->images->first()->id, 'Cover must be the first image');
    }

    public function test_invalid_files_are_skipped_but_valid_ones_attached(): void
    {
        $this->jpeg('1.jpg', 1);
        file_put_contents("{$this->dir}/notes.txt", 'not an image');
        file_put_contents("{$this->dir}/broken.jpg", 'definitely not a jpeg');

        $ad = Ad::factory()->create(['sku' => 'A-2']);
        $result = app(PhotoAttacher::class)->attach($ad, $this->dir);

        $this->assertSame(1, $result['attached']);
        $this->assertCount(2, $result['skipped']);
        $this->assertSame(1, $ad->images()->count());
    }

    public function test_more_than_ten_files_are_capped(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->jpeg("{$i}.jpg", $i);
        }

        $ad = Ad::factory()->create(['sku' => 'A-3']);
        $result = app(PhotoAttacher::class)->attach($ad, $this->dir);

        $this->assertSame(10, $result['attached']);
        $this->assertCount(2, $result['skipped']);
        $this->assertSame(10, $ad->images()->count());
    }

    public function test_oversized_files_are_skipped(): void
    {
        $big = rtrim($this->dir, '/') . '/big.jpg';
        file_put_contents($big, base64_decode(self::JPEG_B64) . str_repeat('x', 6 * 1024 * 1024));

        $this->jpeg('1.jpg', 1);

        $ad = Ad::factory()->create(['sku' => 'A-4']);
        $result = app(PhotoAttacher::class)->attach($ad, $this->dir);

        $this->assertSame(1, $result['attached']);
        $this->assertCount(1, $result['skipped']);
    }

    public function test_replace_strategy_swaps_existing_photos(): void
    {
        $old = 'ads/old/' . uniqid() . '.jpg';
        Storage::disk('public')->put($old, 'old-content');

        $ad = Ad::factory()->create(['sku' => 'A-5']);
        $ad->images()->create(['path' => $old, 'sort_order' => 0]);

        $this->jpeg('1.jpg', 1);
        $this->jpeg('2.jpg', 2);

        $result = app(PhotoAttacher::class)->attach($ad, $this->dir, PhotoAttacher::STRATEGY_REPLACE);

        $this->assertSame(2, $result['attached']);
        $this->assertSame(2, $ad->images()->count());
        $this->assertFalse(Storage::disk('public')->exists($old), 'Old photo file must be deleted');
    }

    public function test_skip_strategy_keeps_existing_photos(): void
    {
        $old = 'ads/old/' . uniqid() . '.jpg';
        Storage::disk('public')->put($old, 'old-content');

        $ad = Ad::factory()->create(['sku' => 'A-6']);
        $ad->images()->create(['path' => $old, 'sort_order' => 0]);

        $this->jpeg('1.jpg', 1);

        $result = app(PhotoAttacher::class)->attach($ad, $this->dir, PhotoAttacher::STRATEGY_SKIP);

        $this->assertSame(0, $result['attached']);
        $this->assertTrue($result['skipped_existing']);
        $this->assertSame(1, $ad->images()->count());
        $this->assertSame($old, $ad->images()->first()->path);
    }

    public function test_missing_directory_attaches_nothing(): void
    {
        $ad = Ad::factory()->create(['sku' => 'A-7']);
        $result = app(PhotoAttacher::class)->attach($ad, "{$this->dir}/missing");

        $this->assertSame(0, $result['attached']);
        $this->assertSame(0, $ad->images()->count());
    }
}