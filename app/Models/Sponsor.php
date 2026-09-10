<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $table = 'sponsor';
    protected $primaryKey = 'id';
    protected $fillable = ['url_image', 'nama'];
    public $timestamps = false;
}
