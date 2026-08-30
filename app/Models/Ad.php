<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $price
 * @property int $stock
 * @property int $views
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $sku
 * @property string $city
 * @property string $condition
 * @property string $status
 * @property bool $is_featured
 * @property int $weight
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property-read \App\Models\AdImage|null $cover_image
 * @property-read string $formatted_price
 * @property-read bool $is_in_stock
 */
class Ad extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'sku',
        'description',
        'price',
        'stock',
        'category_id',
        'city',
        'condition',
        'status',
        'is_featured',
        'weight',
        'dimensions',
        'views',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'is_featured' => 'boolean',
        'views' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ad $ad) {
            if (empty($ad->slug)) {
                $ad->slug = Str::slug($ad->title) . '-' . Str::random(6);
            }
        });
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<AdImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(AdImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Chat, $this> */
    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Report, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function getCoverImageAttribute(): ?AdImage
    {
        return $this->images->first();
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' ₽';
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    public function getMetaTitleAttribute(): ?string
    {
        return $this->attributes['meta_title'] ?? $this->title;
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->attributes['meta_description'] ?? Str::limit(strip_tags($this->description), 160);
    }

    /**
     * Синхронизирует изображения товара с массивом путей (порядок массива = sort_order).
     * Новые файлы из ads/draft/ переносятся в ads/{id}/.
     *
     * @param  array<int, string|null>  $paths
     */
    public function syncImages(array $paths): void
    {
        $paths = array_values(array_filter(array_map(fn ($path): string => trim((string) $path), $paths), fn (string $path): bool => $path !== ''));

        $disk = Storage::disk('public');

        foreach ($paths as $i => $path) {
            if (str_starts_with($path, 'ads/draft/')) {
                $newPath = "ads/{$this->id}/" . Str::uuid() . '.' . (pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');

                if ($disk->move($path, $newPath)) {
                    $paths[$i] = $newPath;
                }
            }
        }

        $this->images->each(function (AdImage $image) use ($disk, $paths): void {
            if (! in_array($image->path, $paths, true)) {
                $image->delete();
                $disk->delete($image->path);
            }
        });

        $existing = $this->images()
            ->whereIn('path', $paths)
            ->pluck('path')
            ->all();

        foreach ($paths as $i => $path) {
            if (in_array($path, $existing, true)) {
                $this->images()
                    ->where('path', $path)
                    ->update(['sort_order' => $i]);

                continue;
            }

            $this->images()->create([
                'path' => $path,
                'sort_order' => $i,
            ]);
        }

        $this->load('images');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->where('status', 'active');
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }
}