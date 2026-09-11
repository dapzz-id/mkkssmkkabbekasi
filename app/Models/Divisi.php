<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasDualIdentifier;

class Divisi extends Model
{
    use HasDualIdentifier;

    protected $table = 'divisi';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['nama_divisi', 'uuid'];
    public $timestamps = false;

    public function user() {
        return $this->hasMany(User::class, 'divisi_uuid', 'uuid');
    }

    public function userById() {
        return $this->hasMany(User::class, 'id_divisi', 'id');
    }

    public function usersByUuid() {
        return $this->hasMany(User::class, 'divisi_uuid', 'uuid');
    }

    public function konten() {
        return $this->hasMany(Konten::class, 'divisi_uuid', 'uuid');
    }

    public function kontenById() {
        return $this->hasMany(Konten::class, 'id_divisi', 'id');
    }

    public function kontenByUuid() {
        return $this->hasMany(Konten::class, 'divisi_uuid', 'uuid');
    }
}
