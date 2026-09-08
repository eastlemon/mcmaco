<?php

namespace App\Console\Commands;

use App\Models\AdImage;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Re-encodes already stored product photos into optimized WebP
 * (max 1600px, ~82 quality). Updates AdImage.path accordingly.
 */
class OptimizeAdImages extends Command
{
    protected $signature = 'ads:optimize-images {--dry : Report only, do not write}';

    protected $description = 'Re-encode existing ad images into optimized WebP';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $optimizer = app(ImageOptimizer::class);

        $images = AdImage::query()
            ->whereNot('path', 'like', '%.webp')
            ->get();

        $dry = (bool) $this->option('dry');
        $done = 0;
        $failed = 0;
        $saved = 0;

        foreach ($images as $image) {
            $abs = $disk->path($image->path);

            if (! is_file($abs)) {
                $this->warn("missing: {$image->path}");
                $failed++;

                continue;
            }

            $bytes = $optimizer->toWebp($abs);

            if ($bytes === null) {
                $this->warn("undecodable: {$image->path}");
                $failed++;

                continue;
            }

            $newPath = preg_replace('/\.[^.]+$/', '.webp', $image->path);
            $oldSize = filesize($abs);

            if ($dry) {
                $this->line("would: {$image->path} ({$oldSize} B) → {$newPath} (" . strlen($bytes) . ' B)');
                $done++;

                continue;
            }

            if (! $disk->put($newPath, $bytes)) {
                $failed++;

                continue;
            }

            $disk->delete($image->path);
            $image->update(['path' => $newPath]);

            $saved += max(0, $oldSize - strlen($bytes));
            $done++;
        }

        $this->info("done: {$done}, failed: {$failed}, saved: " . round($saved / 1024 / 1024, 1) . ' MB');

        return self::SUCCESS;
    }
}
