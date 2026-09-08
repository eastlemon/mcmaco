<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

/**
 * Re-encodes product photos as WebP at web-friendly dimensions.
 *
 * Raw supplier files (multi-megabyte PNGs/JPEGs) are the #1 cause of slow
 * storefront pages. Every newly attached image goes through here and is
 * stored as ~100-400 KB WebP, which all modern browsers serve natively.
 */
class ImageOptimizer
{
    public function __construct(
        private readonly int $maxEdge = 1600,
        private readonly int $quality = 82,
    ) {}

    /**
     * @return string|null WebP bytes, or null when the file is not decodable
     *                     (corrupt / unsupported format).
     */
    public function toWebp(string $absPath): ?string
    {
        try {
            $image = (new ImageManager(new Driver()))->decodePath($absPath);
        } catch (\Throwable) {
            return null;
        }

        try {
            return (string) $image
                ->scaleDown($this->maxEdge, $this->maxEdge)
                ->encodeUsingFormat(Format::WEBP, $this->quality);
        } catch (\Throwable) {
            return null;
        }
    }
}
