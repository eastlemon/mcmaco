<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    public const TYPE_IMPORT = 'import';
    public const TYPE_EXPORT = 'export';

    public const TYPES = [
        self::TYPE_IMPORT => 'Импорт',
        self::TYPE_EXPORT => 'Экспорт',
    ];

    public const FORMAT_CSV = 'csv';
    public const FORMAT_XML = 'xml';
    public const FORMAT_JSON = 'json';

    public const FORMATS = [
        self::FORMAT_CSV => 'CSV',
        self::FORMAT_XML => 'XML',
        self::FORMAT_JSON => 'JSON',
    ];

    protected $fillable = [
        'name',
        'type',
        'adapter',
        'format',
        'config',
        'schedule',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'schedule' => 'string',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Pipeline $pipeline) {
            if (empty($pipeline->config)) {
                $pipeline->config = [];
            }
        });
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<PipelineLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(PipelineLog::class)->latest();
    }

    public function lastRun(): ?PipelineLog
    {
        return $this->logs()->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}