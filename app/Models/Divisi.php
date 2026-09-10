<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    protected $table = 'divisi';
    protected $primaryKey = 'id';
    protected $fillable = ['nama_divisi'];
    public $timestamps = false;

    public function user() {
        return $this->hasMany(User::class, 'id_divisi');
    }

    public function konten() {
        return $this->hasMany(Konten::class, 'id_divisi');
    }
}
