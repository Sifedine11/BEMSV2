<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotImport extends Model
{
    protected $table = 'lots_imports';
    public $timestamps = false;

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

    public function importeur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'importe_par_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneImport::class, 'lot_import_id');
    }
}
