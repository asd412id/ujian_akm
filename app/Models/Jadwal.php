<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    public $dates = [
        'start',
        'end',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'opt' => 'array'
    ];

    public function soals()
    {
        return $this->belongsToMany(Soal::class, 'jadwal_soal');
    }

    public function pesertas()
    {
        return $this->belongsToMany(Peserta::class, 'jadwal_peserta');
    }

    public function logins()
    {
        return $this->hasMany(PesertaLogin::class);
    }

    public function tests()
    {
        return $this->hasMany(PesertaTest::class);
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            $m->soals()->detach();
            $m->pesertas()->detach();
            $m->logins()->delete();
            $m->tests()->delete();
        });
    }
}
