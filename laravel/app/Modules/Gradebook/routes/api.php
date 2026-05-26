<?php

use Gradebook\Http\Controllers\GradebookController;
use Gradebook\Http\Controllers\GradebookExportController;
use Gradebook\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('gradebooks/import', [GradebookController::class, 'import']);
    Route::get('gradebooks/filters', [GradebookController::class, 'filterOptions']);
    Route::get('gradebooks/teachers', [GradebookController::class, 'teachers']);
    Route::get('student/summary', [ResultController::class, 'summary']);
    Route::get('student/results', [ResultController::class, 'index']);
    Route::get('student/semesters', [ResultController::class, 'semesters']);

    Route::get('gradebooks', [GradebookController::class, 'index']);
    Route::get('gradebooks/{gradebook}', [GradebookController::class, 'show']);
    Route::delete('gradebooks/{gradebook}', [GradebookController::class, 'destroy']);
    Route::get('gradebooks/{gradebook}/export', GradebookExportController::class);
});
