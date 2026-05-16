<?php

//TODO: Тест
use App\Modules\City\src\Http\Controllers\UserCityController;
use Illuminate\Support\Facades\Route;

Route::prefix("")->controller(UserCityController::class)->group(function () {
    Route::post("/set-city", 'setCity');
});
