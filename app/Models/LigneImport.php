<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneImport extends Model
{
    protected $table = 'lignes_import';
    public $timestamps = false;

    protected $fillable = [
        'lot_import_id',
        'ligne_brute',
        'statut',
        'message_erreur',
        'course_id',
        'created_at',
    ];

    protected $casts = [
        'ligne_brute' => 'array',
        'created_at'  => 'datetime',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(LotImport::class, 'lot_import_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
