<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $path
 * @property string $url
 */
class AdImage extends Model
{
    protected $fillable = [
        'ad_id',
        'path',
        'sort_order',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Ad, $this> */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
