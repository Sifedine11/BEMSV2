<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UtilisateurController;

Route::middleware(['auth','role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ---- UTILISATEURS (CRUD) ----
        Route::resource('utilisateurs', UtilisateurController::class)
            ->parameters(['utilisateurs' => 'utilisateur']);

        // ---- PLACEHOLDERS pour les autres menus ----
        Route::get('benevoles', fn() => view('admin.benevoles.index'))
            ->name('benevoles.index');

        Route::get('clients', fn() => view('admin.clients.index'))
            ->name('clients.index');

        Route::get('destinations', fn() => view('admin.destinations.index'))
            ->name('destinations.index');
    });
