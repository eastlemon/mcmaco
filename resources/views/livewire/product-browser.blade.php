<div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6">

    {{-- Mobile filter toggle --}}
    <div class="lg:hidden flex items-center justify-between mb-2">
        <button
            wire:click="$toggle('filtersOpen')"
            class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-sm text-sm font-medium"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 12h12M10 20h4"/>
            </svg>
            Фильтры
            @if($this->active_filters_count > 0)
                <span class="bg-amber-600 text-white text-xs rounded-full px-2 py-0.5">{{ $this->active_filters_count }}</span>
            @endif
        </button>

        <select wire:model.live="sort" class="border rounded-lg px-3 py-2 text-sm bg-white">
            <option value="newest">Сначала новые</option>
            <option value="price_asc">Цена ↑</option>
            <option value="price_desc">Цена ↓</option>
            <option value="popular">Популярные</option>
        </select>
    </div>

    {{-- Sidebar Filters --}}
    <aside class="{{ $filtersOpen ? 'block' : 'hidden'}} lg:block">
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-5 lg:sticky lg:top-4">

            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Фильтры</h2>
                @if($this->active_filters_count > 0)
                    <button wire:click="clearFilters" class="text-xs text-amber-600 hover:text-amber-700 font-medium">
                        Сбросить ({{ $this->active_filters_count }})
                    </button>
                @endif
            </div>

            {{-- Search --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Поиск</label>
                <div class="relative">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Название, описание, артикул..."
                        class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    >
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Categories --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Категория</label>
                <select wire:model.live="categoryId" class="w-full border rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">Все категории</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @foreach($category->children as $child)
                            <option value="{{ $child->id }}">— {{ $child->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            {{-- Price range --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    Цена, ₽
                    <span class="text-gray-400 normal-case">
                        @if($minPrice || $maxPrice)
                            ({{ $minPrice ?: $priceRange['min'] }} – {{ $maxPrice ?: $priceRange['max'] }})
                        @endif
                    </span>
                </label>
                <div class="flex items-center gap-2">
                    <input
                        wire:model.live.debounce.300ms="minPrice"
                        type="number"
                        placeholder="{{ $priceRange['min'] }}"
                        class="w-full border rounded-lg px-2 py-1.5 text-sm"
                    >
                    <span class="text-gray-400 text-sm">—</span>
                    <input
                        wire:model.live.debounce.300ms="maxPrice"
                        type="number"
                        placeholder="{{ $priceRange['max'] }}"
                        class="w-full border rounded-lg px-2 py-1.5 text-sm"
                    >
                </div>
            </div>

            {{-- Condition --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Состояние</label>
                <div class="flex gap-2">
                    <button
                        wire:click="$set('condition', '')"
                        class="flex-1 text-sm py-1.5 rounded-lg border transition {{ $condition === '' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white hover:bg-amber-50' }}"
                    >Все</button>
                    <button
                        wire:click="$set('condition', 'new')"
                        class="flex-1 text-sm py-1.5 rounded-lg border transition {{ $condition === 'new' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white hover:bg-amber-50' }}"
                    >Новое</button>
                    <button
                        wire:click="$set('condition', 'used')"
                        class="flex-1 text-sm py-1.5 rounded-lg border transition {{ $condition === 'used' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white hover:bg-amber-50' }}"
                    >Б/у</button>
                </div>
            </div>

            {{-- City --}}
            @if($cities->isNotEmpty())
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Город</label>
                <select wire:model.live="city" class="w-full border rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">Все города</option>
                    @foreach($cities as $cityName)
                        <option value="{{ $cityName }}">{{ $cityName }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Toggles --}}
            <div class="space-y-2 pt-2 border-t">
                <label class="flex items-center gap-2 cursor-pointer text-sm">
                    <input
                        wire:model.live="inStockOnly"
                        type="checkbox"
                        class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                    >
                    <span>Только в наличии</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-sm">
                    <input
                        wire:model.live="featuredOnly"
                        type="checkbox"
                        class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                    >
                    <span>⭐ Хиты продаж</span>
                </label>
            </div>
        </div>
    </aside>

    {{-- Results --}}
    <div>
        {{-- Desktop sort + count --}}
        <div class="hidden lg:flex items-center justify-between mb-4">
            <div class="text-sm text-gray-500">
                Найдено: <span class="font-medium text-gray-700">{{ $ads->total() }}</span> товаров
            </div>
            <select wire:model.live="sort" class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option value="newest">Сначала новые</option>
                <option value="price_asc">Цена ↑</option>
                <option value="price_desc">Цена ↓</option>
                <option value="popular">Популярные</option>
            </select>
        </div>

        {{-- Mobile count --}}
        <div class="lg:hidden text-sm text-gray-500 mb-3">
            Найдено: <span class="font-medium text-gray-700">{{ $ads->total() }}</span> товаров
        </div>

        {{-- Loading overlay --}}
        <div wire:loading.delay class="text-center py-4 text-sm text-gray-400">
            <svg class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Загрузка...
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4" wire:loading.remove.delay>
            @forelse($ads as $ad)
                <x-ads.product-card :ad="$ad" />
            @empty
                <div class="col-span-full bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="text-5xl mb-4">🔍</div>
                    <h3 class="text-lg font-medium text-gray-600 mb-2">Ничего не найдено</h3>
                    <p class="text-sm text-gray-400 mb-4">Попробуйте изменить параметры поиска</p>
                    @if($this->active_filters_count > 0)
                        <button wire:click="clearFilters" class="text-amber-600 hover:text-amber-700 text-sm font-medium">
                            Сбросить фильтры
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $ads->links() }}
        </div>
    </div>
</div>
