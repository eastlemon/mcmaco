<?php

namespace App\Pipelines;

use App\Models\PipelineLog;
use RuntimeException;
use Throwable;

/**
 * Thrown after a pipeline run failure has already been recorded in its
 * PipelineLog, so RunPipelineJob::failed() does not write a duplicate entry.
 */
class PipelineRunFailed extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?PipelineLog $log = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}