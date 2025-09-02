<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $table = 'destinations';

    protected $fillable = [
        'nom',
        'adresse',
        'categorie',
        'code_postal_id',
        'prix_aller',
        'prix_retour',
        'actif',
    ];

    protected $casts = [
        'actif'       => 'boolean',
        'prix_aller'  => 'float',
        'prix_retour' => 'float',
    ];
}
