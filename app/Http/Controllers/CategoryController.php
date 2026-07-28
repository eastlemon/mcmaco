<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        // Collect IDs: the category itself + its children
        $categoryIds = Category::where('id', $category->id)
            ->orWhere('parent_id', $category->id)
            ->pluck('id');

        $query = \App\Models\Ad::query()
            ->with(['category', 'images'])
            ->active()
            ->whereIn('category_id', $categoryIds);

        // Search within category
        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Sort
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

        $ads = $query->paginate(24)->withQueryString();

        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => route('ads.index')],
            ['name' => $category->name, 'url' => route('categories.show', $category->slug)],
        ];

        // Schema.org BreadcrumbList
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)->map(fn ($crumb, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ])->values()->toArray(),
        ];

        return view('categories.show', compact('category', 'ads', 'breadcrumbs', 'breadcrumbSchema'));
    }
}