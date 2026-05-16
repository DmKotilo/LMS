<?php

//TODO: Тест
use App\Modules\Cart\src\Http\Controllers\CartController;
use App\Modules\Referral\src\Http\Controllers\ReferralController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;
use Review\Http\Controllers\ReviewReactionController;

Route::get('/get-link', [ReferralController::class, 'getReferralLink']);
Route::post('/register', [ReferralController::class, 'registerWithReferral']);
Route::post('/reward', [ReferralController::class, 'processReferralReward']);
