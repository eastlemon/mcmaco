<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AdImageController extends Controller
{
    /**
     * AJAX загрузка изображения для объявления.
     */
    public function store(Request $request, Ad $ad): JsonResponse
    {
        if ($request->user()?->id !== $ad->user_id) {
            abort(403);
        }

        if ($ad->images()->count() >= 10) {
            return response()->json(['message' => 'Maximum 10 images allowed.'], 422);
        }

        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $file = $data['image'];
        $dir = "ads/{$ad->id}";
        $baseName = (string) Str::uuid();
        $originalPath = "{$dir}/{$baseName}.jpg";
        $thumbPath = "{$dir}/{$baseName}_thumb.jpg";

        $manager = new ImageManager(new Driver());
        $image = $manager->decodePath($file->getPathname());

        if ($image->width() > 2048) {
            $image = $image->scale(width: 2048);
        }

        $image->save(Storage::disk('public')->path($originalPath), quality: 85);

        $manager->decodePath($file->getPathname())
            ->cover(200, 200)
            ->save(Storage::disk('public')->path($thumbPath), quality: 85);

        $adImage = AdImage::query()->create([
            'ad_id' => $ad->id,
            'path' => $originalPath,
            'sort_order' => 0,
        ]);

        return response()->json([
            'id' => $adImage->id,
            'url' => Storage::url($originalPath),
            'thumb_url' => Storage::url($thumbPath),
        ]);
    }

    /**
     * Удаление изображения.
     */
    public function destroy(Request $request, Ad $ad, AdImage $adImage): JsonResponse
    {
        if ($request->user()?->id !== $ad->user_id) {
            abort(403);
        }

        if ($adImage->ad_id !== $ad->id) {
            abort(404);
        }

        $originalPath = $adImage->path;
        $thumbPath = str_replace('.jpg', '_thumb.jpg', $originalPath);

        Storage::disk('public')->delete([$originalPath, $thumbPath]);
        $adImage->delete();

        return response()->json(['ok' => true]);
    }
}
