<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Peserta extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['is_login'];

    protected $hidden = [
        'password',
        'remember_token',
        'token',
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function jadwals()
    {
        return $this->belongsToMany(Jadwal::class);
    }

    public function logins()
    {
        return $this->hasMany(PesertaLogin::class);
    }
}
