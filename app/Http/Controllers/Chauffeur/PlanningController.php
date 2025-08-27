<?php

namespace App\Http\Controllers\Chauffeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class PlanningController extends Controller
{
    /**
     * Affiche les courses du chauffeur pour une semaine donnée.
     * Paramètre optionnel ?semaine=YYYY-MM-DD (un jour de la semaine, idéalement le lundi).
     */
    public function semaine(Request $request)
    {
        $chauffeur = Auth::user();

        // Ancre de semaine (par défaut: aujourd'hui), normalisée au lundi
        $ancre = $request->filled('semaine')
            ? Carbon::parse($request->query('semaine'))
            : Carbon::now();

        $debut = $ancre->copy()->startOfWeek(Carbon::MONDAY);
        $fin   = $ancre->copy()->endOfWeek(Carbon::SUNDAY);

        // Récupère toutes les courses attribuées à ce chauffeur dans l'intervalle
        // (on n'utilise PAS ->with('client') pour éviter une erreur si la relation n'existe pas encore)
        $courses = Course::where('chauffeur_id', $chauffeur->id)
            ->whereBetween('date_service', [$debut->toDateString(), $fin->toDateString()])
            ->orderBy('date_service')
            ->orderBy('heure_depart')
            ->get();

        // Liste des 7 jours au format YYYY-MM-DD (clés attendues côté Blade)
        $jours = collect(range(0, 6))
            ->map(fn ($i) => $debut->copy()->addDays($i)->toDateString());

        // Groupement **normalisé** sur YYYY-MM-DD pour éviter les mismatches de clés
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
        ]);
    }
}
