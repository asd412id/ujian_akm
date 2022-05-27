<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaTest extends Model
{
    use HasFactory;

    protected $fillable = ['pscore'];

    public $dates = [
        'start',
        'end',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'option' => 'array',
        'correct' => 'array',
        'relation' => 'array',
        'label' => 'array',
    ];

    public function itemSoal()
    {
        return $this->belongsTo(ItemSoal::class);
    }
    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }
}
