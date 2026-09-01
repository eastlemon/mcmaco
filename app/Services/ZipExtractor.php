<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

/**
 * Safe ZIP extraction for import pipelines.
 *
 * Whitelist: only image files are extracted (jpg/jpeg/png/webp).
 * Zip-slip (path traversal) entries abort the extraction.
 */
class ZipExtractor
{
    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function __construct(
        private readonly int $maxFiles = 5000,
        private readonly int $maxBytes = 524288000, // 500 MB total
    ) {}

    /**
     * @param string $zipPath Absolute path to the ZIP archive.
     * @param string $destDir Absolute path of the destination directory (created if needed).
     * @return array{files: int, bytes: int}
     *
     * @throws RuntimeException
     */
    public function extract(string $zipPath, string $destDir): array
    {
        if (! is_file($zipPath)) {
            throw new RuntimeException("ZIP archive not found: {$zipPath}");
        }

        $zip = new ZipArchive();
        $code = $zip->open($zipPath);

        if ($code !== true) {
            throw new RuntimeException("Cannot open ZIP archive (code {$code}): {$zipPath}");
        }

        $files = 0;
        $bytes = 0;

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                if ($name === false || $name === '' || str_ends_with($name, '/')) {
                    continue; // directory entries
                }

                if ($this->isUnsafe($name)) {
                    throw new RuntimeException("Unsafe entry path in ZIP: {$name}");
                }

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                    continue; // silently skip non-image files
                }

                $stat = $zip->statIndex($i);

                if ($files + 1 > $this->maxFiles) {
                    throw new RuntimeException("Too many files in ZIP (limit {$this->maxFiles})");
                }

                if ($bytes + (int) ($stat['size'] ?? 0) > $this->maxBytes) {
                    throw new RuntimeException("ZIP content exceeds size limit ({$this->maxBytes} bytes)");
                }

                $dest = rtrim($destDir, '/') . '/' . $name;

                if (! is_dir($dir = dirname($dest))) {
                    mkdir($dir, 0755, true);
                }

                $content = $zip->getFromIndex($i);

                if ($content === false) {
                    throw new RuntimeException("Failed to read ZIP entry: {$name}");
                }

                if (file_put_contents($dest, $content) === false) {
                    throw new RuntimeException("Failed to write extracted file: {$dest}");
                }

                $files++;
                $bytes += strlen($content);
            }
        } finally {
            $zip->close();
        }

        return ['files' => $files, 'bytes' => $bytes];
    }

    /**
     * Reject absolute paths, drive letters and any ".." traversal segments.
     */
    private function isUnsafe(string $name): bool
    {
        $normalized = str_replace('\\', '/', $name);

        if (str_starts_with($normalized, '/') || preg_match('/^[a-z]:/i', $normalized)) {
            return true;
        }

        $segments = explode('/', $normalized);

        return in_array('..', $segments, true);
    }
}