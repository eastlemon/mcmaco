<?php

namespace Tests\Unit;

use App\Services\ZipExtractor;
use RuntimeException;
use Tests\TestCase;

class ZipExtractorTest extends TestCase
{
    private const JPEG_B64 = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwcJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPDs0NDT/wAALCAABAAEBAREA/8QAFAABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AmAA//9k=';

    private string $tmp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmp = sys_get_temp_dir() . '/zip-extractor-' . uniqid();
        mkdir($this->tmp, 0755, true);
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->tmp));

        parent::tearDown();
    }

    private function buildZip(string $filename, array $entries): string
    {
        $path = "{$this->tmp}/{$filename}";

        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE);

        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();

        return $path;
    }

    private function jpeg(): string
    {
        return base64_decode(self::JPEG_B64);
    }

    public function test_extracts_only_image_files(): void
    {
        $zip = $this->buildZip('photos.zip', [
            'photos/A-1/1-cover.jpg' => $this->jpeg(),
            'photos/A-1/2.jpg' => $this->jpeg(),
            'readme.txt' => 'ignore me',
            'photo.png' => $this->jpeg(),
        ]);

        $dest = "{$this->tmp}/dest";
        $stats = (new ZipExtractor())->extract($zip, $dest);

        $this->assertSame(3, $stats['files']);
        $this->assertFileExists("{$dest}/photos/A-1/1-cover.jpg");
        $this->assertFileExists("{$dest}/photos/A-1/2.jpg");
        $this->assertFileExists("{$dest}/photo.png");
        $this->assertFileDoesNotExist("{$dest}/readme.txt");
    }

    public function test_zip_slip_entries_are_rejected(): void
    {
        $zip = $this->buildZip('evil.zip', [
            'photos/ok.jpg' => $this->jpeg(),
            '../evil.jpg' => $this->jpeg(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsafe entry path');

        (new ZipExtractor())->extract($zip, "{$this->tmp}/dest");
    }

    public function test_nested_traversal_entries_are_rejected(): void
    {
        $zip = $this->buildZip('evil2.zip', [
            'photos/../../evil.jpg' => $this->jpeg(),
        ]);

        $this->expectException(RuntimeException::class);

        (new ZipExtractor())->extract($zip, "{$this->tmp}/dest");
    }

    public function test_file_count_limit_is_enforced(): void
    {
        $entries = [];

        for ($i = 1; $i <= 4; $i++) {
            $entries["photos/x/{$i}.jpg"] = $this->jpeg();
        }

        $zip = $this->buildZip('many.zip', $entries);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Too many files');

        (new ZipExtractor(maxFiles: 3, maxBytes: 1024 * 1024))->extract($zip, "{$this->tmp}/dest");
    }

    public function test_total_size_limit_is_enforced(): void
    {
        $zip = $this->buildZip('big.zip', [
            'photos/x/1.jpg' => $this->jpeg(),
            'photos/x/2.jpg' => $this->jpeg(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('size limit');

        (new ZipExtractor(maxFiles: 10, maxBytes: 16))->extract($zip, "{$this->tmp}/dest");
    }

    public function test_missing_archive_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not found');

        (new ZipExtractor())->extract("{$this->tmp}/missing.zip", "{$this->tmp}/dest");
    }
}