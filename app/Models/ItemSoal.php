<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemSoal extends Model
{
    use HasFactory;

    public $casts = [
        'options' => 'array',
        'corrects' => 'array',
        'relations' => 'array',
        'labels' => 'array',
        'opt' => 'array',
    ];
}
