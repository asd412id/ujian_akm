<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    public function item_soals()
    {
        return $this->hasMany(ItemSoal::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            $m->item_soals()->delete();
        });
    }
}
