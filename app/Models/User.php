<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasFactory, HasApiTokens, Notifiable, CanResetPassword;
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_divisi',
        'name',
        'username',
        'password',
        'role',
        'alamat',
        'email',
        'remember_token'
    ];

    public function divisi() {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function konten() {
        return $this->hasMany(Konten::class, 'id_user');
    }

    /**
     * Send the password reset notification using our branded,
     * queued notification instead of Laravel's default.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}
