<?php

use Illuminate\Support\Facades\Route;
use User\Http\Controllers\PasswordController;
use User\Http\Controllers\ProfileController;

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::patch('profile', [ProfileController::class, 'update']);
    Route::put('profile/password', [PasswordController::class, 'update']);
});
