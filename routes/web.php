<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectionController;

// Accueil -> login direct
Route::redirect('/', '/login')->name('accueil');

// Tableau de bord générique : redirige selon le rôle réel
Route::middleware('auth')->get('/tableau', [RedirectionController::class, 'apresLogin'])->name('tableau');
Route::middleware('auth')->get('/dashboard', [RedirectionController::class, 'apresLogin'])->name('dashboard');

// Inclusion des routes par rôle (protégées dans chaque fichier)
foreach (['admin', 'telephoniste', 'coordinateur', 'chauffeur'] as $fichier) {
    $path = __DIR__."/roles/{$fichier}.php";
    if (file_exists($path)) require $path;
}

// Auth (Breeze/Fortify/etc.)
require __DIR__.'/auth.php';
