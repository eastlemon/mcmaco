<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use Illuminate\Http\Request;

class AdController extends Controller
{
    /**
     * Публичный список объявлений с фильтрами.
     */
    public function index(Request $request)
    {
        $query = Ad::query()
            ->with(['category', 'user', 'images'])
            ->where('status', 'active');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($city = $request->string('city')->trim()->toString()) {
            $query->where('city', 'like', "%{$city}%");
        }

        if ($condition = $request->string('condition')->trim()->toString()) {
            $query->where('condition', $condition);
        }

        if ($minPrice = $request->integer('min_price')) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice = $request->integer('max_price')) {
            $query->where('price', '<=', $maxPrice);
        }

        $sort = $request->string('sort')->toString();
        if ($sort === 'price_asc') {
            $query->orderBy('price');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } else {
            $query->latest();
        }

        $ads = $query->paginate(20)->withQueryString();
        $categories = Category::query()->orderBy('name')->get();

        return view('ads.index', compact('ads', 'categories'));
    }

    /**
     * Карточка объявления.
     */
    public function show(Ad $ad)
    {
        if ($ad->status !== 'active') {
            abort(404);
        }

        $ad->load(['category', 'user', 'images']);

        return view('ads.show', compact('ad'));
    }
}
