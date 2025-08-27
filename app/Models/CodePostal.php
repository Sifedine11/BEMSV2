<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodePostal extends Model
{
    protected $table = 'codes_postaux';
    protected $fillable = [
        'code_postal','localite','tarif_forfaitaire',
    ];
}
