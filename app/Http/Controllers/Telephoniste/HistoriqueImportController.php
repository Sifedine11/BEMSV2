<?php

namespace App\Http\Controllers\Telephoniste;

use App\Http\Controllers\Controller;
use App\Models\LotImport;
use App\Models\Course;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class HistoriqueImportController extends Controller
{
    public function index()
    {
        // Pagination (12 par page), avec l’utilisateur importeur
        $lots = LotImport::with('importeur')
            ->orderByDesc('commence_le')
            ->orderByDesc('id')
            ->paginate(12);

        // Vue : resources/views/telephoniste/imports/historique.blade.php
        return view('telephoniste.imports.historique', compact('lots'));
    }

    /**
     * Affiche le contenu d’un lot (même esprit que la prévisualisation) :
     * - entête du lot
     * - journal
     * - lignes importées (courses rattachées au lot)
     */
    public function show(LotImport $lot)
    {
        // Récupère les courses du lot
        $courses = Course::query()
            ->where('lot_import_id', $lot->id)
            ->orderBy('date_service')
            ->orderBy('heure_depart')
            ->get([
                'id',
                'date_service',
                'heure_depart',
                'client_id',
                'adresse_depart',
                'adresse_arrivee',
                'type_course',
                'prix_aller_calcule',
                'statut',
            ]);

        // Map clients pour afficher "Nom Prénom" au lieu de l'id
        $clients = collect();
        $clientIds = $courses->pluck('client_id')->filter()->unique()->values();
        if ($clientIds->isNotEmpty()) {
            $clients = DB::table('clients')
                ->select(['id', DB::raw("COALESCE(nom,'') as nom"), DB::raw("COALESCE(prenom,'') as prenom")])
                ->whereIn('id', $clientIds)
                ->get()
                ->keyBy('id');
        }

        // Vue : resources/views/telephoniste/imports/show.blade.php
        return view('telephoniste.imports.show', [
            'lot'     => $lot->load('importeur'),
            'courses' => $courses,
            'clients' => $clients,
        ]);
    }
}
