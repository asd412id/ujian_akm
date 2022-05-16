<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaLogin extends Model
{
    use HasFactory;

    public $dates = [
        'start',
        'end',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'soal' => 'array'
    ];

    public function soals()
    {
        $ids_order = implode(',', $this->soal);
        $query = Soal::whereIn('id', $this->soal)
            ->orderByRaw("FIELD(id, $ids_order)");

        return $query;
    }
}
