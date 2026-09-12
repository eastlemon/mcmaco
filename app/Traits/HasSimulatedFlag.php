<?php

namespace App\Traits;

/**
 * Marks an Eloquent model as "simulatable".
 *
 * When attached to a model that has the is_simulated column, this trait
 * automatically sets is_simulated=true on create — unless explicitly
 * overridden. This prevents accidental contamination of real data by
 * code that forgets to set the flag.
 *
 * Usage:
 *   class User extends Authenticatable {
 *       use HasSimulatedFlag;
 *   }
 *
 *   User::create([...]);             // is_simulated = false (default)
 *   User::factory()->simulated()->create();  // is_simulated = true
 *
 * The trait also provides a scope to filter real vs simulated records.
 */
trait HasSimulatedFlag
{
    /**
     * Bootstrap the trait. Force is_simulated = false on new records
     * unless the caller explicitly set it to true.
     */
    protected static function bootHasSimulatedFlag(): void
    {
        static::creating(function (self $model) {
            if (! array_key_exists('is_simulated', $model->attributesToArray())) {
                $model->is_simulated = false;
            }
        });
    }

    /**
     * Initialize the trait for a model instance. Ensures the attribute
     * always has a value (avoid undefined index when accessed via ArrayAccess).
     */
    protected function initializeHasSimulatedFlag(): void
    {
        if (! array_key_exists('is_simulated', $this->attributes)) {
            $this->attributes['is_simulated'] = false;
        }
    }

    /** @param \Illuminate\Database\Eloquent\Builder<self> $query */
    public function scopeSimulated(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_simulated', true);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<self> $query */
    public function scopeReal(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_simulated', false);
    }
}
