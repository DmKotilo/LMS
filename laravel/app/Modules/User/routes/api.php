<?php

use Authorization\Http\Controllers\ChangeEmailController;
use Illuminate\Support\Facades\Route;
use User\Http\Controllers\PasswordController;
use User\Http\Controllers\ProfileController;
use User\Http\Controllers\TeacherController;

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::patch('profile', [ProfileController::class, 'update']);
    Route::put('profile/password', [PasswordController::class, 'update']);
    Route::post('profile/email', [ChangeEmailController::class, 'store'])->middleware('throttle:6,1');

    Route::post('admin/teachers', [TeacherController::class, 'store']);
});
