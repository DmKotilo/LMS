<?php

//TODO: Тест
use App\Modules\Cart\src\Http\Controllers\CartController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;
use Review\Http\Controllers\ReviewReactionController;


Route::post('/{review}/toggle', [ReviewReactionController::class, 'toggle'])->name('reviews.toggle');

