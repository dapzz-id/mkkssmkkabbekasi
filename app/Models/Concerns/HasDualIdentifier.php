<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait HasDualIdentifier
{
    /**
     * Boot the dual identifier trait.
     * Automatically assigns a canonical UUID v4 on creating if uuid column exists and is empty.
     * Automatically keeps transitional UUID foreign keys in sync with integer foreign keys.
     */
    protected static function bootHasDualIdentifier(): void
    {
        static::creating(function ($model) {
            // 1. Auto-generate canonical UUID v4 if not already assigned
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if ($model->getKeyName() === 'uuid') {
                $model->{$model->getKeyName()} = $model->uuid;
            }
        });
    }

    /**
     * Scope query to find record by canonical UUID string.
     * Preserves backwards compatibility for consumers while safely preventing
     * unknown column errors if a legacy integer is passed.
     *
     * @param Builder $query
     * @param mixed $identifier
     * @return Builder
     */
    public function scopeWhereIdentifier(Builder $query, mixed $identifier): Builder
    {
        $idString = (string) $identifier;

        // Canonical UUID v4 pattern
        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $idString)) {
            return $query->where($this->getTable() . '.uuid', $idString);
        }

        // If a legacy numeric identifier is passed after id column is dropped,
        // match nothing safely without throwing SQL column not found error.
        if (ctype_digit($idString)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->getTable() . '.uuid', $idString);
    }

    /**
     * Find a model by its canonical UUID string.
     *
     * @param mixed $identifier
     * @param array $columns
     * @return static|null
     */
    public static function findByIdentifier(mixed $identifier, array $columns = ['*']): ?static
    {
        return static::whereIdentifier($identifier)->first($columns);
    }

    /**
     * Find a model by its canonical UUID string or throw 404.
     *
     * @param mixed $identifier
     * @param array $columns
     * @return static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findByIdentifierOrFail(mixed $identifier, array $columns = ['*']): static
    {
        return static::whereIdentifier($identifier)->firstOrFail($columns);
    }

    /**
     * Retrieve the model for a bound value with UUID support.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        return $this->whereIdentifier($value)->first();
    }

    /**
     * Get the query to use for model restoration when deserializing queued jobs/notifications.
     * Uses canonical UUID primary key.
     */
    public function newQueryForRestoration($ids)
    {
        if (is_array($ids)) {
            return $this->newQueryWithoutScopes()->whereIn($this->getKeyName(), $ids);
        }

        $idString = (string) $ids;
        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $idString)) {
            return $this->newQueryWithoutScopes()->where($this->getKeyName(), $idString);
        }

        return $this->newQueryWithoutScopes()->where($this->getKeyName(), $ids);
    }

    /**
     * Create a new Eloquent query builder for the model with UUID primary key whereKey support.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function whereKey($id)
            {
                if ($id instanceof \Illuminate\Database\Eloquent\Model) {
                    $id = $id->getKey();
                }

                if (is_array($id) || $id instanceof \Illuminate\Contracts\Support\Arrayable) {
                    $array = is_array($id) ? $id : $id->toArray();
                    return $this->whereIn($this->model->getTable() . '.' . $this->model->getKeyName(), $array);
                }

                return $this->where($this->model->getTable() . '.' . $this->model->getKeyName(), '=', (string) $id);
            }
        };
    }
}
