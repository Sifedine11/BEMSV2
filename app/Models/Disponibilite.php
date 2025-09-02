<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Disponibilite extends Model
{
    /**
     * On ne verrouille rien : on s’adapte au schéma existant.
     * (Évite les erreurs de mass assignment si les colonnes diffèrent.)
     */
    protected $guarded = [];

    /**
     * La plupart des tables de dispos n’ont pas de timestamps.
     * Mets à true si ta table en a.
     */
    public $timestamps = false;

    /**
     * Cache interne du nom de table résolu.
     */
    protected static ?string $resolvedTable = null;

    /**
     * Choix dynamique du nom de table :
     * essaie plusieurs candidats courants et retient le premier qui existe.
     */
    public function getTable()
    {
        if (static::$resolvedTable !== null) {
            return static::$resolvedTable;
        }

        $candidates = [
            'creneaux_disponibilite',
            'creneau_disponibilite',
            'disponibilites',
            'disponibilite',
        ];

        foreach ($candidates as $t) {
            try {
                if (Schema::hasTable($t)) {
                    return static::$resolvedTable = $t;
                }
            } catch (\Throwable $e) {
                // ignore et continue
            }
        }

        // Fallback vers le comportement Eloquent par défaut
        return static::$resolvedTable = parent::getTable();
    }
}
