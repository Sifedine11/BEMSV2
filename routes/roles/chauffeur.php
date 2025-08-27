<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chauffeur\DisponibiliteController;
use App\Http\Controllers\Chauffeur\PlanningController;

Route::middleware(['auth','role:chauffeur'])
    ->prefix('chauffeur')
    ->name('chauffeur.')
    ->group(function () {

        // --- Mon planning (semaine)
        Route::get('planning/semaine', [PlanningController::class, 'semaine'])
            ->name('planning.semaine');

        // --- Disponibilités
        Route::get('disponibilites', [DisponibiliteController::class, 'index'])
            ->name('dispo.index');

        // Ajouter un créneau (avec option "toutes les semaines")
        Route::post('disponibilites', [DisponibiliteController::class, 'store'])
            ->name('dispo.store');

        // Supprimer un créneau précis
        Route::delete('disponibilites/{id}', [DisponibiliteController::class, 'destroy'])
            ->name('dispo.destroy');

        // Supprimer tous les créneaux d’une date
        Route::delete('disponibilites-jour', [DisponibiliteController::class, 'destroyJour'])
            ->name('dispo.destroyJour');
    });
