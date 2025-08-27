<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Telephoniste\ImportCoursController;
use App\Http\Controllers\Telephoniste\HistoriqueImportController;

Route::middleware(['auth','role:telephoniste'])
    ->prefix('telephoniste')
    ->name('telephoniste.')
    ->group(function () {

        Route::get('imports', [HistoriqueImportController::class, 'index'])
            ->name('imports.index');

        Route::get('imports/nouveau', [ImportCoursController::class, 'index'])
            ->name('import.nouveau');

        Route::post('imports/previsualiser', [ImportCoursController::class, 'previsualiser'])
            ->name('import.previsualiser');

        Route::post('imports/confirmer', [ImportCoursController::class, 'confirmer'])
            ->name('import.confirmer');
    });
