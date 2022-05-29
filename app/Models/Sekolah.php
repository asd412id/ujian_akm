<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'opt'
    ];
    public $casts = ['opt' => 'array'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function mapels()
    {
        return $this->hasMany(Mapel::class);
    }

    public function pesertas()
    {
        return $this->hasMany(Peserta::class);
    }

    public function soals()
    {
        return $this->hasMany(Soal::class);
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function getKopAttribute()
    {
        return $this->opt && isset($this->opt['kop']) ? $this->opt['kop'] : null;
    }

    public function getLogoAttribute()
    {
        return $this->opt && isset($this->opt['logo']) ? $this->opt['logo'] : null;
    }

    public function getLimitLoginAttribute()
    {
        return $this->opt && isset($this->opt['limit_login']) ? $this->opt['limit_login'] : false;
    }
    public function getRestrictTestAttribute()
    {
        return $this->opt && isset($this->opt['restrict_test']) ? $this->opt['restrict_test'] : false;
    }
}
