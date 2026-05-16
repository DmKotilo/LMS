<?php

//TODO: Тест
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\Promotion\Http\Controllers\PromotionController::class, 'index'])->name('index');
Route::get('/{promotion}', [\Promotion\Http\Controllers\PromotionController::class, 'show'])->name('show');

