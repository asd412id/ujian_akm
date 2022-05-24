<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaLogin extends Model
{
    use HasFactory;

    protected $fillable = ['reset', 'end', 'created_at'];

    public $dates = [
        'start',
        'end',
        'created_at',
        'updated_at',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public $casts = [
        'soal' => 'array'
    ];

    public function soals()
    {
        $ids_order = implode(',', $this->soal);
        $query = ItemSoal::whereIn('id', $this->soal)
            ->orderByRaw("FIELD(id, $ids_order)");

        return $query->get();
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function tests()
    {
        return $this->hasMany(PesertaTest::class, 'login_id');
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            $m->tests()->delete();
        });
    }
}
