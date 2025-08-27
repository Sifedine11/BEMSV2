<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'nom','prenom','genre','adresse','code_postal_id','localite',
        'tel_mobile','tel_fixe','contact_urgence_nom','contact_urgence_tel',
        'moyens_auxiliaires','niveau_aide','consignes_chauffeur','consignes_detail',
        'date_naissance','actif','carte_legale_tmr',
    ];

    public function codePostal()
    {
        return $this->belongsTo(CodePostal::class, 'code_postal_id');
    }
}
