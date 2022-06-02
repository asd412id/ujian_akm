<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Soal extends Model
{
    use HasFactory;

    public $casts = ['opt' => 'array'];

    public function item_soals()
    {
        return $this->hasMany(ItemSoal::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function jadwals()
    {
        return $this->belongsToMany(Jadwal::class, 'jadwal_soal');
    }

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function getExcelAttribute()
    {
        return isset($this->opt['excel']) ? $this->opt['excel'] : null;
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            $m->item_soals()->delete();
            if ($m->excel && Storage::exists($m->excel)) {
                Storage::delete($m->excel);
            }
            if (Storage::disk('public')->exists('uploads/' . userFolder() . '/' . Str::slug($m->name))) {
                Storage::disk('public')->deleteDirectory('uploads/' . userFolder() . '/' . Str::slug($m->name));
                Storage::disk('public')->deleteDirectory('thumbs/' . userFolder() . '/' . Str::slug($m->name));
            }
        });
    }
}
