<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotImport extends Model
{
    protected $table = 'lots_imports';
    public $timestamps = false; // la table n'a pas created_at / updated_at

    protected $fillable = [
        'fichier_source',
        'importe_par_id',
        'commence_le',
        'termine_le',
        'lignes_total',
        'lignes_ok',
        'lignes_erreur',
        'journal',
    ];

    protected $casts = [
        'commence_le'  => 'datetime',
        'termine_le'   => 'datetime',
        'journal'      => 'array',
        'lignes_total' => 'integer',
        'lignes_ok'    => 'integer',
        'lignes_erreur'=> 'integer',
    ];

    /** Utilisateur qui a lancé l'import */
    public function importeur(): BelongsTo
    {
        // Attention: ton modèle d’utilisateur s’appelle Utilisateur (pas User)
        return $this->belongsTo(Utilisateur::class, 'importe_par_id');
    }

    /** Lignes d'import liées */
    public function lignes(): HasMany
    {
        return $this->hasMany(LigneImport::class, 'lot_import_id');
    }
}
