<?php

//TODO: Тест
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;



Route::controller(\Combination\Http\Controllers\CombinationController::class)->prefix('')->name('')->group(function () {
    Route::get('', 'index')->name('index');
    Route::get('/{combination}', 'show')->name('show');
});
