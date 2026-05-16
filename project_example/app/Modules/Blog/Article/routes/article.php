<?php

//TODO: Тест
use App\Modules\Blog\Article\src\Http\Controllers\NewsController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;


Route::get('/news',[NewsController::class,'index'])->name('index');
Route::get('/news/selection/{selection}',[NewsController::class,'selectionDetail'])->name('selection.show');
Route::get('/news/article/{article}',[NewsController::class,'articleDetail'])->name('article.show');
