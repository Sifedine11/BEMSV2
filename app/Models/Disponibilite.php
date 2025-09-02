<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Disponibilite extends Model
{

    protected $guarded = [];


    public $timestamps = false;


    protected static ?string $resolvedTable = null;


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

            }
        }

        return static::$resolvedTable = parent::getTable();
    }
}
