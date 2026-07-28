<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use Illuminate\Http\Request;

class AdController extends Controller
{
    /**
     * Главная витрина: популярные + новинки + категории.
     */
    public function index(Request $request)
    {
        $query = Ad::query()
            ->with(['category', 'user', 'images'])
            ->active()
            ->inStock();

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
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
        } elseif ($sort === 'popular') {
            $query->orderByDesc('views');
        } else {
            $query->latest();
        }

        $ads = $query->paginate(20)->withQueryString();
        $categories = Category::roots()->get();

        return view('ads.index', compact('ads', 'categories'));
    }

    /**
     * Карточка товара с schema.org Product.
     */
    public function show(string $slug)
    {
        $ad = Ad::with(['category', 'user', 'images'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        $ad->increment('views');

        $related = Ad::query()
            ->where('category_id', $ad->category_id)
            ->where('id', '!=', $ad->id)
            ->active()
            ->inStock()
            ->limit(4)
            ->get();

        $schema = $this->buildProductSchema($ad);

        return view('ads.show', compact('ad', 'related', 'schema'));
    }

    private function buildProductSchema(Ad $ad): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $ad->title,
            'description' => strip_tags($ad->description),
            'sku' => $ad->sku ?? "MC-{$ad->id}",
            'brand' => ['@type' => 'Brand', 'name' => 'mcmaco'],
            'offers' => [
                '@type' => 'Offer',
                'price' => $ad->price,
                'priceCurrency' => 'RUB',
                'availability' => $ad->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => route('ads.show', $ad->slug),
            ],
        ];

        $images = $ad->images->map(fn ($img) => asset('storage/' . $img->path))->take(5);
        if ($images->isNotEmpty()) {
            $schema['image'] = $images->toArray();
        }

        return $schema;
    }
}