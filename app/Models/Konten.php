<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasDualIdentifier;

class Konten extends Model
{
    use HasDualIdentifier;

    protected $table = 'konten';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function user() {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function userById() {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function userByUuid() {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function divisi() {
        return $this->belongsTo(Divisi::class, 'divisi_uuid', 'uuid');
    }

    public function divisiById() {
        return $this->belongsTo(Divisi::class, 'id_divisi', 'id');
    }

    public function divisiByUuid() {
        return $this->belongsTo(Divisi::class, 'divisi_uuid', 'uuid');
    }

    /**
     * Accessor to ensure media URLs always use /storage/ rather than /public/storage/
     * This fixes compatibility between cPanel/production and local development (php artisan serve).
     */
    public function getUrlMediaAttribute($value)
    {
        if (!$value) {
            return $value;
        }

        return str_replace(
            ['\/public\/storage\/', '/public/storage/'],
            ['\/storage\/', '/storage/'],
            $value
        );
    }
}
