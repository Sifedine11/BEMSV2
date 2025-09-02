<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Benevole extends Model
{
    protected $table = 'benevoles';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'tel_mobile',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];
}
