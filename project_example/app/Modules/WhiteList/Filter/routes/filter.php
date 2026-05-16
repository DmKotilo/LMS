<?php

//TODO: Тест
use App\Modules\WhiteList\Filter\src\Http\Controllers\FilterController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;


Route::controller(FilterController::class)->group(function () {
    Route::post('/save','saveFilter')->name('saveFilter');
    Route::delete('/delete/{id}','deleteFilter')->name('deleteFilter');
});
