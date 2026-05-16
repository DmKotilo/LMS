<?php

//TODO: Тест
use App\Modules\Cart\src\Http\Controllers\CartController;
use Combo\Http\Controllers\ComboController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

// Маршрут для списка ВСЕХ комбо-наборов (GET /combo/)
Route::get('/', [ComboController::class, 'index'])->name('index');

// Маршрут для детальной страницы конкретного комбо-набора (GET /combo/{combo})
Route::get('/{combo}', [ComboController::class, 'show'])->name('show');
