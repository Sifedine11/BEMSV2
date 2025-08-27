<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Coordinateur\AttributionController;

Route::middleware(['auth', 'role:coordinateur'])
    ->prefix('coordinateur')
    ->name('coordinateur.')
    ->group(function () {
        Route::get('courses/a-attribuer', [AttributionController::class, 'index'])
            ->name('courses.a_attribuer');

        Route::get('courses/planifiees', [AttributionController::class, 'planifiees'])
            ->name('courses.planifiees');

        Route::post('courses/{course}/attribuer', [AttributionController::class, 'attribuer'])
            ->name('courses.attribuer');
    });
