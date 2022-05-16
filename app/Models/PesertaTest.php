<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaTest extends Model
{
    use HasFactory;

    public $dates = [
        'start',
        'end',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'options' => 'array',
        'corrects' => 'array',
        'relations' => 'array',
    ];
}
