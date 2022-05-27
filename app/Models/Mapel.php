<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_mapel', 'mapel_id', 'user_id');
    }

    public function soals()
    {
        return $this->hasMany(Soal::class);
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            $m->users()->detach();
        });
    }
}
