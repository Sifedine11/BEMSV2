<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $table = 'courses';
    public $timestamps = true;

    protected $fillable = [
        'date_service',
        'heure_depart',
        'heure_arrivee',
        'type_course',
        'statut',
        'client_id',
        'chauffeur_id',
        'destination_id',
        'adresse_depart',
        'code_postal_depart_id',
        'adresse_arrivee',
        'code_postal_arrivee_id',
        'temps_attente_min',
        'heure_classement',
        'distance_km',
        'distance_source_km',
        'prix_aller_calcule',
        'prix_retour_calcule',
        'utiliser_prix_communique',
        'commentaires',
        'lot_import_id',
        'reserve_le',
    ];

    protected $casts = [
        'date_service' => 'date',
        'heure_depart' => 'datetime:H:i:s',
        'heure_arrivee'=> 'datetime:H:i:s',
        'reserve_le'   => 'datetime',
        'utiliser_prix_communique' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function chauffeur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'chauffeur_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }
}
