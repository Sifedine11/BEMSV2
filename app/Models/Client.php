<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Schema;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'nom',
        'prenom',
        'genre',
        'adresse',
        'code_postal_id',
        'localite',
        'tel_mobile',
        'tel_fixe',
        'contact_urgence_nom',
        'contact_urgence_tel',
        'moyens_auxiliaires',
        'niveau_aide',
        'consignes_chauffeur',
        'consignes_detail',
        'date_naissance',
        'actif',
        'carte_legale_tmr',
    ];

    protected $casts = [
        'actif'            => 'boolean',
        'carte_legale_tmr' => 'boolean',
        'date_naissance'   => 'date',
    ];


    protected function detectChauffeurPivotKey(string $pivotTable): string
    {
        $fallback = 'chauffeur_id';

        if (! Schema::hasTable($pivotTable)) {
            return $fallback;
        }

        $cols = Schema::getColumnListing($pivotTable);

        if (in_array('chauffeur_id', $cols, true))   return 'chauffeur_id';
        if (in_array('utilisateur_id', $cols, true)) return 'utilisateur_id';
        if (in_array('user_id', $cols, true))        return 'user_id';

        return $fallback;
    }

    /** Chauffeurs préférés (pivot: clients_chauffeurs_preferes) */
    public function chauffeursPreferes()
    {
        $pivot = 'clients_chauffeurs_preferes';
        $chauffeurKey = $this->detectChauffeurPivotKey($pivot);

        return $this->belongsToMany(
            Utilisateur::class,
            $pivot,
            'client_id',
            $chauffeurKey
        );
    }

    /** Chauffeurs refusés (pivot: clients_chauffeurs_refuses) */
    public function chauffeursRefuses()
    {
        $pivot = 'clients_chauffeurs_refuses';
        $chauffeurKey = $this->detectChauffeurPivotKey($pivot);

        return $this->belongsToMany(
            Utilisateur::class,
            $pivot,
            'client_id',
            $chauffeurKey
        );
    }
}
