<?php

//TODO: Тест

use File\Http\Controllers\FileController;
use Home\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('main-page');


Route::get('/translations-check', [HomeController::class, 'translationsCheck'])->name('translations.check');
