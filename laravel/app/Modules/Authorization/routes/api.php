<?php

use Authorization\Http\Controllers\LoginController;
use Authorization\Http\Controllers\LogoutController;
use Authorization\Http\Controllers\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', LoginController::class)
        ->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::post('logout', LogoutController::class);
        Route::get('me', MeController::class);
    });
});
