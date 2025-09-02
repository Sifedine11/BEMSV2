<?php

namespace App\Observers;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseObserver
{

    public function saving(Course $course): void
    {
        // 1) Extraire les CP des adresses
        $cpDepart  = $this->extractCp((string)($course->adresse_depart ?? ''));
        $cpArrivee = $this->extractCp((string)($course->adresse_arrivee ?? ''));

        // 2) Résoudre id + tarif des codes postaux (si table/colonnes présentes)
        $depart  = $this->findCodePostal($cpDepart);
        $arrivee = $this->findCodePostal($cpArrivee);

        if ($depart && isset($depart->id)) {
            $course->code_postal_depart_id = $depart->id;
        }
        if ($arrivee && isset($arrivee->id)) {
            $course->code_postal_arrivee_id = $arrivee->id;
        }

        // 3) Prix = max(tarif départ, tarif arrivée), si non forcé par "utiliser_prix_communique"
        if (!($course->utiliser_prix_communique ?? false)) {
            $tarifs = [];
            if ($depart && property_exists($depart, 'tarif_forfaitaire') && $depart->tarif_forfaitaire !== null) {
                $tarifs[] = (float) $depart->tarif_forfaitaire;
            }
            if ($arrivee && property_exists($arrivee, 'tarif_forfaitaire') && $arrivee->tarif_forfaitaire !== null) {
                $tarifs[] = (float) $arrivee->tarif_forfaitaire;
            }

            if (!empty($tarifs)) {
                $course->prix_aller_calcule = max($tarifs);
            }
        }
    }

    /**
     * Extrait un CP (4 chiffres) depuis une adresse.
     * Exemple attendu: "..., 1024 Ecublens VD".
     */
    protected function extractCp(string $adresse): ?string
    {
        if ($adresse === '') {
            return null;
        }

        // Cas le plus courant: après la virgule
        if (preg_match('/,\s*([0-9]{4})\b/u', $adresse, $m)) {
            return $m[1];
        }

        // Secours: n'importe où comme token à 4 chiffres
        if (preg_match('/\b([0-9]{4})\b/u', $adresse, $m)) {
            return $m[1];
        }

        return null;
        }

    /**
     * Retourne l’enregistrement CP (id, tarif…) si présent.
     */
    protected function findCodePostal(?string $cp)
    {
        if (!$cp) {
            return null;
        }
        if (!Schema::hasTable('codes_postaux')) {
            return null;
        }

        $cols = Schema::getColumnListing('codes_postaux');
        if (!in_array('code_postal', $cols, true)) {
            return null;
        }

        $select = ['id', 'code_postal'];
        if (in_array('tarif_forfaitaire', $cols, true)) {
            $select[] = 'tarif_forfaitaire';
        }

        return DB::table('codes_postaux')
            ->where('code_postal', $cp)
            ->orderBy('id', 'asc') // au cas où plusieurs lignes pour un même CP
            ->first($select);
    }
}
