<?php

//TODO: Тест
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::prefix("")->controller(\App\Modules\Localization\src\Http\Controllers\UserLangController::class)->group(function () {
    Route::post("/set-lang", 'setLang');
});
