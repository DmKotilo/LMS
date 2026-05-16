<?php

//TODO: Тест
use App\Modules\Blog\Recipe\src\Http\Controllers\RecipeController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;


Route::get('/', [RecipeController::class, 'index'])->name('index');
Route::get('/{recipe}', [RecipeController::class, 'show'])->name('show');
