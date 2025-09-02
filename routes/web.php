<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\Telephoniste\HistoriqueImportController;


// Accueil -> login direct
Route::redirect('/', '/login')->name('accueil');

// Tableau de bord commun pour tous les rôles authentifiés
Route::middleware('auth')->get('/tableau', [DashboardController::class, 'index'])->name('tableau');
Route::middleware('auth')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Paramètres (auth requis)
Route::middleware('auth')->group(function () {
    Route::get('/parametres', [ParametreController::class, 'edit'])->name('parametres.edit');
    Route::put('/parametres', [ParametreController::class, 'update'])->name('parametres.update');
});



// Inclusion des routes par rôle (protégées dans chaque fichier)
foreach (['admin', 'telephoniste', 'coordinateur', 'chauffeur'] as $fichier) {
    $path = __DIR__."/roles/{$fichier}.php";
    if (file_exists($path)) {
        require $path;
    }
}

// Auth (Breeze/Fortify/etc.)
require __DIR__.'/auth.php';
