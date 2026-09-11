<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * Список избранных объявлений.
     */
    public function index(Request $request): View
    {
        $favorites = Favorite::query()
            ->with(['ad.category', 'ad.images'])
            ->where('user_id', $request->user()->id)
            ->whereHas('ad')
            ->latest()
            ->paginate(20);

        return view('favorites.index', compact('favorites'));
    }

    /**
     * Добавить в избранное.
     */
    public function store(Request $request, Ad $ad): RedirectResponse|JsonResponse
    {
        Favorite::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'ad_id' => $ad->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['favorite' => true]);
        }

        return back();
    }

    /**
     * Удалить из избранного.
     */
    public function destroy(Request $request, Ad $ad): RedirectResponse|JsonResponse
    {
        Favorite::query()
            ->where('user_id', $request->user()->id)
            ->where('ad_id', $ad->id)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['favorite' => false]);
        }

        return back();
    }
}
