<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasDualIdentifier;

class Pimpinan extends Model
{
    use HasFactory, HasDualIdentifier;

    protected $table = 'pimpinan';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'nama',
        'jabatan',
        'foto',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Scope query to only include active leaders.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to order leaders by display_order (urutan) ascending, then created_at, then uuid.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('created_at', 'asc')->orderBy('uuid', 'asc');
    }

    /**
     * Accessor to get clean, accessible photo URL.
     */
    public function getFotoUrlAttribute(): string
    {
        if (empty($this->foto)) {
            return asset('img/ic_MKKS.png');
        }

        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return $this->foto;
        }

        // Clean any accidental /public/storage/ prefix
        $cleaned = str_replace(
            ['\/public\/storage\/', '/public/storage/'],
            ['\/storage\/', '/storage/'],
            $this->foto
        );

        if (str_starts_with($cleaned, '/storage/')) {
            return $cleaned;
        }

        return '/storage/' . ltrim($cleaned, '/');
    }
}
