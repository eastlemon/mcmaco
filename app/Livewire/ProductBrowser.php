<?php

namespace App\Livewire;

use App\Models\Ad;
use App\Models\Category;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductBrowser extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'category_id', except: '')]
    public string $categoryId = '';

    #[Url(except: '')]
    public string $city = '';

    #[Url(except: '')]
    public string $condition = '';

    #[Url(except: '')]
    public string $minPrice = '';

    #[Url(except: '')]
    public string $maxPrice = '';

    #[Url(except: '')]
    public bool $inStockOnly = false;

    #[Url(except: '')]
    public bool $featuredOnly = false;

    #[Url(as: 'sort', except: 'newest')]
    public string $sort = 'newest';

    #[Url(except: false)]
    public bool $filtersOpen = false;

    public function updating($property): void
    {
        // Reset page when any filter changes
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search', 'categoryId', 'city', 'condition',
            'minPrice', 'maxPrice', 'inStockOnly', 'featuredOnly',
        ]);
        $this->sort = 'newest';
        $this->resetPage();
    }

    public function getActiveFiltersCountProperty(): int
    {
        $count = 0;
        if ($this->search) $count++;
        if ($this->categoryId) $count++;
        if ($this->city) $count++;
        if ($this->condition) $count++;
        if ($this->minPrice) $count++;
        if ($this->maxPrice) $count++;
        if ($this->inStockOnly) $count++;
        if ($this->featuredOnly) $count++;
        return $count;
    }

    public function render()
    {
        $query = Ad::query()
            ->with(['category', 'images'])
            ->active();

        if ($this->inStockOnly) {
            $query->where('stock', '>', 0);
        }

        if ($this->featuredOnly) {
            $query->where('is_featured', true);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        if ($this->categoryId) {
            // Include subcategory filtering
            $categoryIds = Category::where('id', $this->categoryId)
                ->orWhere('parent_id', $this->categoryId)
                ->pluck('id');
            $query->whereIn('category_id', $categoryIds);
        }

        if ($this->city) {
            $query->where('city', $this->city);
        }

        if ($this->condition) {
            $query->where('condition', $this->condition);
        }

        if ($this->minPrice !== '') {
            $query->where('price', '>=', (int) $this->minPrice);
        }

        if ($this->maxPrice !== '') {
            $query->where('price', '<=', (int) $this->maxPrice);
        }

        match ($this->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('views'),
            default => $query->latest(),
        };

        $ads = $query->paginate(24);
        $categories = Category::roots()->with('children')->orderBy('name')->get();
        $cities = Ad::active()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
        $priceRange = [
            'min' => (int) (Ad::active()->min('price') ?? 0),
            'max' => (int) (Ad::active()->max('price') ?? 100000),
        ];

        return view('livewire.product-browser', compact('ads', 'categories', 'cities', 'priceRange'));
    }
}
