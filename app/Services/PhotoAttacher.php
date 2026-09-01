<?php

namespace App\Services;

use App\Models\Ad;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attaches product photos from an extracted import run directory
 * (photos/{SKU}/...) to an Ad using the same storage convention
 * as the Filament admin (ads/{id}/uuid.ext, cover = sort_order 0).
 *
 * File naming inside photos/{SKU}/:
 *   1-cover.jpg   <- numeric prefix defines order, "-cover" marks the cover
 *   2.jpg
 *   3.jpg
 */
class PhotoAttacher
{
    public const STRATEGY_REPLACE = 'replace';
    public const STRATEGY_SKIP = 'skip';

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function __construct(
        private readonly int $maxPerAd = 10,
        private readonly int $maxBytes = 5242880, // 5 MB, mirrors the admin form
    ) {}

    /**
     * Attach photos from a directory (the product's own photos/{SKU} folder).
     *
     * @param string $dir Absolute path with the product's photo files.
     * @param string $strategy replace|skip
     * @return array{attached: int, skipped: array<int, string>, skipped_existing: bool}
     */
    public function attach(Ad $ad, string $dir, string $strategy = self::STRATEGY_REPLACE): array
    {
        if ($strategy === self::STRATEGY_SKIP && $ad->images()->exists()) {
            return ['attached' => 0, 'skipped' => [], 'skipped_existing' => true];
        }

        $files = $this->scanAndSort($dir);
        $skipped = [];
        $paths = [];

        foreach ($files as $file) {
            if (count($paths) >= $this->maxPerAd) {
                $skipped[] = "{$file}: лимит {$this->maxPerAd} фото";
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                $skipped[] = "{$file}: не изображение";
                continue;
            }

            $size = filesize($file);

            if ($size === false || $size > $this->maxBytes) {
                $skipped[] = "{$file}: больше {$this->maxBytes} байт";
                continue;
            }

            if (@getimagesize($file) === false) {
                $skipped[] = "{$file}: повреждённое изображение";
                continue;
            }

            $path = 'ads/' . $ad->id . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, fopen($file, 'rb'));
            $paths[] = $path;
        }

        if ($paths !== []) {
            $ad->syncImages($paths);
        }

        return [
            'attached' => count($paths),
            'skipped' => $skipped,
            'skipped_existing' => false,
        ];
    }

    /**
     * Scan the directory for files and define display order:
     * cover-marked files first (by number), then the rest by numeric prefix.
     *
     * @return list<string> absolute file paths
     */
    private function scanAndSort(string $dir): array
    {
        if (! is_dir($dir)) {
            return [];
        }

        $files = array_values(array_filter(
            glob(rtrim($dir, '/') . '/*') ?: [],
            fn (string $path): bool => is_file($path),
        ));

        usort($files, function (string $a, string $b): int {
            return $this->orderKey($a) <=> $this->orderKey($b);
        });

        return $files;
    }

    /**
     * @return array{int, int, string} [cover flag, numeric prefix, name]
     */
    private function orderKey(string $path): array
    {
        $name = basename($path);
        $lower = mb_strtolower($name);
        $isCover = str_contains($lower, '-cover') ? 0 : 1;
        preg_match('/^(\d+)/', $name, $m);

        return [$isCover, isset($m[1]) ? (int) $m[1] : PHP_INT_MAX, $lower];
    }
}