<?php

//TODO: Тест
use App\Modules\Cart\src\Http\Controllers\CartController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/',[\Faq\Http\Controllers\FaqController::class,'index'])->name('faq.index');

