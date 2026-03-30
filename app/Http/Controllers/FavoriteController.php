<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Favorite;
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
            ->with('ad.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('favorites.index', compact('favorites'));
    }

    /**
     * Добавить в избранное.
     */
    public function store(Request $request, Ad $ad): RedirectResponse
    {
        Favorite::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'ad_id' => $ad->id,
        ]);

        return back();
    }

    /**
     * Удалить из избранного.
     */
    public function destroy(Request $request, Ad $ad): RedirectResponse
    {
        Favorite::query()
            ->where('user_id', $request->user()->id)
            ->where('ad_id', $ad->id)
            ->delete();

        return back();
    }
}
