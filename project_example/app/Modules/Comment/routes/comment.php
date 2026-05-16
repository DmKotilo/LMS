<?php

//TODO: Тест
use App\Modules\Cart\src\Http\Controllers\CartController;
use Comment\Http\Controllers\CommentController;
use Comment\Http\Controllers\CommentReactionController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;


Route::post('/{comment}/toggle', [CommentReactionController::class, 'toggle'])->name('comments.toggle');
Route::post('/store', [CommentController::class, 'store'])->name('store');

