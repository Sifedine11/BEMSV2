<?php

namespace App\Http\Controllers\Chauffeur;

use App\Http\Controllers\Controller;
use App\Models\CreneauDisponibilite;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DisponibiliteController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $today = Carbon::today();
        $now   = Carbon::now();

        // Date sélectionnée pour l’éditeur en haut (par défaut aujourd’hui)
        $dateSelection = $request->input('date', $today->toDateString());

        // Créneaux DU JOUR sélectionné (comme avant)
        $creneauxDuJour = CreneauDisponibilite::query()
            ->where('utilisateur_id', $userId)
            ->whereDate('date_jour', $dateSelection)
            ->orderBy('heure_debut')
            ->get();


        // Règle d’affichage :
        //  - date_jour > today  => toutes
        //  - date_jour = today  => seulement celles dont heure_fin >= maintenant
        $prochainesDispos = CreneauDisponibilite::query()
            ->where('utilisateur_id', $userId)
            ->where(function ($q) use ($today, $now) {
                $q->whereDate('date_jour', '>', $today)
                  ->orWhere(function ($qq) use ($today, $now) {
                      $qq->whereDate('date_jour', '=', $today)
                         ->where('heure_fin', '>=', $now->format('H:i:s'));
                  });
            })
            ->orderBy('date_jour')
            ->orderBy('heure_debut')
            ->get();

        return view('chauffeur.disponibilites.index', [
            'dateSelection'   => $dateSelection,
            'creneauxDuJour'  => $creneauxDuJour,
            'prochainesDispos'=> $prochainesDispos,
        ]);
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'date'        => ['required', 'date'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin'   => ['required', 'date_format:H:i', 'after:heure_debut'],
            'repeter'     => ['nullable', Rule::in(['1'])],
        ]);

        $date = Carbon::parse($validated['date'])->toDateString();
        $hd   = $validated['heure_debut'] . ':00';
        $hf   = $validated['heure_fin']   . ':00';

        // Créneau du jour
        CreneauDisponibilite::firstOrCreate([
            'utilisateur_id' => $userId,
            'date_jour'      => $date,
            'heure_debut'    => $hd,
            'heure_fin'      => $hf,
        ]);

        // Répéter toutes les semaines
        if ($request->boolean('repeter')) {
            // on crée +4 semaines
            $start = Carbon::parse($date)->copy();
            for ($i = 1; $i <= 4; $i++) {
                $d = $start->copy()->addWeeks($i)->toDateString();
                CreneauDisponibilite::firstOrCreate([
                    'utilisateur_id' => $userId,
                    'date_jour'      => $d,
                    'heure_debut'    => $hd,
                    'heure_fin'      => $hf,
                ]);
            }
        }

        return back()->with('ok', 'Disponibilité ajoutée.');
    }

    public function destroy($id)
    {
        $userId = Auth::id();

        $slot = CreneauDisponibilite::where('utilisateur_id', $userId)->findOrFail($id);
        $slot->delete();

        return back()->with('ok', 'Créneau supprimé.');
    }

    public function destroyJour(Request $request)
    {
        $userId = Auth::id();
        $request->validate(['date' => 'required|date']);
        $date = Carbon::parse($request->input('date'))->toDateString();

        CreneauDisponibilite::where('utilisateur_id', $userId)
            ->whereDate('date_jour', $date)
            ->delete();

        return back()->with('ok', 'Tous les créneaux de la date ont été supprimés.');
    }
}
