<?php

use Illuminate\Support\Facades\Route;

// Admin Controllers
use App\Http\Controllers\Admin\UtilisateurController;
use App\Http\Controllers\Admin\BenevoleController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\ClientController;

Route::middleware(['auth','role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ---- UTILISATEURS (CRUD) ----
        Route::resource('utilisateurs', UtilisateurController::class)
            ->parameters(['utilisateurs' => 'utilisateur']);

        // ---- BENEVOLES (CRUD) ----
        Route::resource('benevoles', BenevoleController::class)
            ->parameters(['benevoles' => 'benevole']);

        // ---- DESTINATIONS (CRUD) ----
        Route::resource('destinations', DestinationController::class)
            ->parameters(['destinations' => 'destination']);

        // ---- CLIENTS (CRUD) ----
        Route::resource('clients', ClientController::class)
            ->parameters(['clients' => 'client']);
    });
