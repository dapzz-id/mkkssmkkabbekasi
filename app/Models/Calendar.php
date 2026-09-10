<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $table = 'calendar';
    protected $primaryKey = 'id';
    protected $fillable = ['event_name', 'event_date'];
    public $timestamps = false;
}
