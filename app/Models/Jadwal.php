<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'start',
        'end',
        'reset',
    ];

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

    public function item_soals($sid)
    {
        return ItemSoal::whereIn('soal_id', $sid);
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
        self::creating(function ($m) {
            $m->uuid = \Str::uuid();
        });
        self::updating(function ($m) {
            $m->uuid = $m->uuid ?? \Str::uuid();
        });
        self::deleting(function ($m) {
            $m->soals()->detach();
            $m->pesertas()->detach();
            $m->logins()->delete();
            $m->tests()->delete();
        });
    }
}
