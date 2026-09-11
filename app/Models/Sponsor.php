<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasDualIdentifier;

class Sponsor extends Model
{
    use HasDualIdentifier;

    protected $table = 'sponsor';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['uuid', 'url_image', 'nama'];
    public $timestamps = false;
}
