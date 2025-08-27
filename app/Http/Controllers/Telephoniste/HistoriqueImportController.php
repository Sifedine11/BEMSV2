<?php

namespace App\Http\Controllers\Telephoniste;

use App\Http\Controllers\Controller;
use App\Models\LotImport;

class HistoriqueImportController extends Controller
{
    public function index()
    {
        // Pagination (12 par page), avec l’utilisateur importeur
        $lots = LotImport::with('importeur')
            ->orderByDesc('commence_le')
            ->orderByDesc('id')
            ->paginate(12);

        // Vue utilisée: resources/views/telephoniste/imports/historique.blade.php
        return view('telephoniste.imports.historique', compact('lots'));
    }
}
