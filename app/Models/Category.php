<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'meta_title',
        'meta_description',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<self, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<self, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Ad, $this> */
    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function getMetaTitleAttribute(): ?string
    {
        return $this->attributes['meta_title'] ?? $this->name;
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->attributes['meta_description'] ?? 'Товары в категории «' . $this->name . '» — mcmaco';
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->orderBy('name');
    }
}