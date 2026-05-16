<?php

//TODO: Тест
use App\Modules\WhiteList\List\src\Http\Controllers\WhitelistController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;


Route::controller(WhitelistController::class)->group(function () {
    Route::post('/add','add')->name('add');
    Route::delete('/remove','remove')->name('remove');
});
