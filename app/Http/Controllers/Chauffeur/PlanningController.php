<?php

namespace App\Http\Controllers\Chauffeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Course;

class PlanningController extends Controller
{
    /**
     * Affiche les courses du chauffeur pour une semaine donnée.
     * Paramètre optionnel ?semaine=YYYY-MM-DD (un jour de la semaine, idéalement le lundi).
     */
    public function semaine(Request $request)
    {
        // Pour les libellés de date en français (isoFormat / translatedFormat)
        Carbon::setLocale('fr');

        $chauffeur = Auth::user();

        // Ancre de semaine (par défaut: aujourd'hui), normalisée au lundi
        $ancre = $request->filled('semaine')
            ? Carbon::parse($request->query('semaine'))
            : Carbon::now();

        $debut = $ancre->copy()->startOfWeek(Carbon::MONDAY);
        $fin   = $ancre->copy()->endOfWeek(Carbon::SUNDAY);

        // Récupère toutes les courses attribuées à ce chauffeur dans l'intervalle
        $courses = Course::where('chauffeur_id', $chauffeur->id)
            ->whereBetween('date_service', [$debut->toDateString(), $fin->toDateString()])
            ->orderBy('date_service')
            ->orderBy('heure_depart')
            ->get();

        // Liste des 7 jours au format YYYY-MM-DD
        $jours = collect(range(0, 6))
            ->map(fn ($i) => $debut->copy()->addDays($i)->toDateString());

        // Groupement sur YYYY-MM-DD
        $grouped = $courses->groupBy(function ($c) {
            return Carbon::parse($c->date_service)->toDateString();
        });

        // Assure que chaque jour existe (même vide) et trie par heure
        $byDay = collect();
        foreach ($jours as $j) {
            $liste = ($grouped->get($j) ?? collect())
                ->sortBy('heure_depart')
                ->values();
            $byDay->put($j, $liste);
        }

        // === Données clients pour la modale "Détails" ===
        $clients = collect();
        if (Schema::hasTable('clients')) {
            $clientIds = $courses->pluck('client_id')->filter()->unique()->values();
            if ($clientIds->isNotEmpty()) {
                // Colonnes utiles à afficher
                $colsVoulu = [
                    'id','nom','prenom','adresse','localite','code_postal_id',
                    'tel_mobile','tel_fixe','moyens_auxiliaires','niveau_aide'
                ];
                $colsDispo = array_intersect($colsVoulu, Schema::getColumnListing('clients'));
                if (!in_array('id', $colsDispo, true)) {
                    $colsDispo[] = 'id';
                }

                $clients = DB::table('clients')
                    ->whereIn('id', $clientIds)
                    ->get($colsDispo)
                    ->keyBy('id');
            }
        }

        // Map des codes postaux (si présent)
        $cpMap = collect();
        if (Schema::hasTable('codes_postaux')) {
            $col = in_array('code_postal', Schema::getColumnListing('codes_postaux')) ? 'code_postal' : null;
            if ($col) {
                $cpMap = DB::table('codes_postaux')->select('id', $col)->get()->keyBy('id')->map(function($r) use ($col) {
                    return $r->{$col};
                });
            }
        }

        // Nombre total d'items de la semaine (pour l'info "Aucune course...")
        $totalSemaine = $byDay->flatten(1)->count();

        return view('chauffeur.planning.semaine', [
            'debut'        => $debut,
            'fin'          => $fin,
            'jours'        => $jours,
            'byDay'        => $byDay,
            'totalSemaine' => $totalSemaine,
            'prevWeek'     => $debut->copy()->subWeek()->toDateString(),
            'nextWeek'     => $debut->copy()->addWeek()->toDateString(),

            // ajoutés pour la modale
            'clients'      => $clients,
            'cpMap'        => $cpMap,
        ]);
    }
}
