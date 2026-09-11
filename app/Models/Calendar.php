<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasDualIdentifier;

class Calendar extends Model
{
    use HasDualIdentifier;

    protected $table = 'calendar';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['uuid', 'event_name', 'event_date'];
    public $timestamps = false;
}
