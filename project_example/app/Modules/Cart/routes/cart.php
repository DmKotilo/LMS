<?php

//TODO: Тест
use App\Modules\Cart\src\Http\Controllers\CartController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CartController::class, 'index'])->name('index');
Route::post('/add', [CartController::class, 'add'])->name('add');
Route::post('/retry-order/{order}', [CartController::class, 'retryOrder'])->name('retryOrder');
Route::delete('/remove', [CartController::class, 'remove'])->name('remove');
Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
Route::post('/add-recipe/{recipe}', [CartController::class, 'addRecipeToCart']);
Route::post('/check-gift', [CartController::class, 'checkGift'])->name('checkGift');
