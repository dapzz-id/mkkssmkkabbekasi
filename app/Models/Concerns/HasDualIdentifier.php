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
            // 1. Auto-generate UUID if not already assigned
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if ($model->getKeyName() === 'uuid') {
                $model->{$model->getKeyName()} = $model->uuid;
            }

            // 2. Synchronize FKs for dual-write safety
            static::syncTransitionalForeignKeys($model);
        });

        static::created(function ($model) {
            // Automatically populate legacy auto-increment id into in-memory model attribute
            if (empty($model->id) && !empty($model->uuid)) {
                $legacyId = $model->newQuery()->where('uuid', $model->uuid)->value('id');
                if ($legacyId) {
                    $model->setAttribute('id', (int) $legacyId);
                    $model->syncOriginalAttribute('id');
                }
            }
        });

        static::updating(function ($model) {
            static::syncTransitionalForeignKeys($model);
        });
    }

    /**
     * Synchronize transitional UUID foreign keys and integer foreign keys bi-directionally.
     */
    protected static function syncTransitionalForeignKeys($model): void
    {
        // For User model: sync id_divisi <-> divisi_uuid
        if ($model instanceof \App\Models\User) {
            if ($model->isDirty('divisi_uuid') && !empty($model->divisi_uuid)) {
                $divisiId = \App\Models\Divisi::where('uuid', $model->divisi_uuid)->value('id');
                if ($divisiId) {
                    $model->id_divisi = $divisiId;
                }
            } elseif (isset($model->id_divisi) && ($model->isDirty('id_divisi') || empty($model->divisi_uuid))) {
                $divisiUuid = \App\Models\Divisi::where('id', $model->id_divisi)->value('uuid');
                if ($divisiUuid) {
                    $model->divisi_uuid = $divisiUuid;
                }
            }
        }

        // For Konten model: sync id_user <-> user_uuid and id_divisi <-> divisi_uuid
        if ($model instanceof \App\Models\Konten) {
            // User FK
            if ($model->isDirty('user_uuid') && !empty($model->user_uuid)) {
                $userId = \App\Models\User::where('uuid', $model->user_uuid)->value('id');
                if ($userId) {
                    $model->id_user = $userId;
                }
            } elseif (isset($model->id_user) && ($model->isDirty('id_user') || empty($model->user_uuid))) {
                $userUuid = \App\Models\User::where('id', $model->id_user)->value('uuid');
                if ($userUuid) {
                    $model->user_uuid = $userUuid;
                }
            }

            // Divisi FK
            if ($model->isDirty('divisi_uuid') && !empty($model->divisi_uuid)) {
                $divisiId = \App\Models\Divisi::where('uuid', $model->divisi_uuid)->value('id');
                if ($divisiId) {
                    $model->id_divisi = $divisiId;
                }
            } elseif (isset($model->id_divisi) && ($model->isDirty('id_divisi') || empty($model->divisi_uuid))) {
                $divisiUuid = \App\Models\Divisi::where('id', $model->id_divisi)->value('uuid');
                if ($divisiUuid) {
                    $model->divisi_uuid = $divisiUuid;
                }
            }
        }
    }

    /**
     * Scope query to find record by either integer ID or canonical UUID string.
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
            return $query->where('uuid', $idString);
        }

        // Strict numeric ID pattern
        if (ctype_digit($idString)) {
            return $query->where('id', (int) $idString);
        }

        // Fallback for safety
        return $query->where('id', $identifier)->orWhere('uuid', $identifier);
    }

    /**
     * Find a model by either its integer primary key or its canonical UUID string.
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
     * Find a model by either its integer primary key or its canonical UUID string or throw 404.
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
     * Retrieve the model for a bound value with dual-identifier support.
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
     * Supports both canonical UUIDs and legacy numeric IDs.
     */
    public function newQueryForRestoration($ids)
    {
        if (is_array($ids)) {
            return $this->newQueryWithoutScopes()->whereIn($this->getKeyName(), $ids);
        }

        $idString = (string) $ids;
        if (ctype_digit($idString)) {
            return $this->newQueryWithoutScopes()->where('id', (int) $idString);
        }

        return $this->newQueryWithoutScopes()->where($this->getKeyName(), $ids);
    }

    /**
     * Create a new Eloquent query builder for the model with dual-identifier whereKey support.
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
                    if (!empty($array) && ctype_digit((string) reset($array))) {
                        return $this->whereIn($this->model->getTable() . '.id', $array);
                    }
                    return parent::whereKey($id);
                }

                $idString = (string) $id;
                if (ctype_digit($idString)) {
                    return $this->where($this->model->getTable() . '.id', '=', (int) $idString);
                }

                return parent::whereKey($id);
            }
        };
    }
}
