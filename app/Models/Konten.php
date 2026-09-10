<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konten extends Model
{
    protected $table = 'konten';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function user() {
        return $this->belongsTo(User::class, 'id_user'); // Add comma between class and field name
    }

    public function divisi() {
        return $this->belongsTo(Divisi::class, 'id_divisi'); // Add comma between class and field name
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
