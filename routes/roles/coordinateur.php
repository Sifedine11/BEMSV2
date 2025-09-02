<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Coordinateur\AttributionController;

Route::middleware(['auth', 'role:coordinateur'])
    ->prefix('coordinateur')
    ->name('coordinateur.')
    ->group(function () {

        // A attribuer
        Route::get('courses/a-attribuer', [AttributionController::class, 'index'])
            ->name('courses.a_attribuer');

        // Planifiées
        Route::get('courses/planifiees', [AttributionController::class, 'planifiees'])
            ->name('courses.planifiees');

        // Attribuer
        Route::post('courses/{course}/attribuer', [AttributionController::class, 'attribuer'])
            ->name('courses.attribuer');

        // Nouveau : liste chauffeurs + disponibilité pour une course (JSON)
        Route::get('courses/{course}/chauffeurs', [AttributionController::class, 'chauffeursForCourse'])
            ->name('courses.chauffeurs');
    });
