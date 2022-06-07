<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function getOperatorAttribute()
    {
        return $this->users()->where('role', 0)->first();
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            foreach ($m->users as $d) {
                $d->delete();
            }
            foreach ($m->mapels as $d) {
                $d->delete();
            }
            foreach ($m->pesertas as $d) {
                $d->delete();
            }
            foreach ($m->soals as $d) {
                $d->delete();
            }
            foreach ($m->jadwals as $d) {
                $d->delete();
            }

            Storage::disk('public')->deleteDirectory('uploads/' . generateUserFolder($m->id));
            Storage::disk('public')->deleteDirectory('thumbs/' . generateUserFolder($m->id));
        });
    }
}
