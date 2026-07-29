<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipelineLog extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_PENDING => 'Ожидает',
        self::STATUS_RUNNING => 'Выполняется',
        self::STATUS_SUCCESS => 'Успешно',
        self::STATUS_FAILED => 'Ошибка',
    ];

    protected $fillable = [
        'pipeline_id',
        'status',
        'message',
        'processed',
        'created',
        'updated',
        'errors',
        'details',
    ];

    protected $casts = [
        'processed' => 'integer',
        'created' => 'integer',
        'updated' => 'integer',
        'errors' => 'integer',
        'details' => 'array',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }
}