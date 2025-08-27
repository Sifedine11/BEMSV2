<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreneauDisponibilite extends Model
{
    protected $table = 'creneaux_disponibilite';

    protected $fillable = [
        'utilisateur_id',
        'date_jour',
        'heure_debut',
        'heure_fin',
    ];

    protected $casts = [
        'date_jour'   => 'date',
        'heure_debut' => 'datetime:H:i',
        'heure_fin'   => 'datetime:H:i',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}
