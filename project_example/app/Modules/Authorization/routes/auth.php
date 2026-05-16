<?php

use Authorization\Http\Controllers\AuthenticatedSessionController;
use Authorization\Http\Controllers\ChangeEmailController;
use Authorization\Http\Controllers\ChangePhoneController;
use Authorization\Http\Controllers\CodeVerificationController;
use Authorization\Http\Controllers\ConfirmablePasswordController;
use Authorization\Http\Controllers\EmailVerificationNotificationController;
use Authorization\Http\Controllers\EmailVerificationPromptController;
use Authorization\Http\Controllers\NewPasswordController;
use Authorization\Http\Controllers\Oauth\VkontakteController;
use Authorization\Http\Controllers\Oauth\YandexController;
use Authorization\Http\Controllers\PasswordController;
use Authorization\Http\Controllers\PasswordResetLinkController;
use Authorization\Http\Controllers\RegisteredUserController;
use Authorization\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use User\Http\Controllers\SubscriptionController;

Route::middleware('guest')->group(function () {


    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::post('register-by-phone', [RegisteredUserController::class, 'storeByPhone']);

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password-phone', [NewPasswordController::class, 'storeByPhone'])
        ->name('password.store.phone');



});

Route::prefix("auth/vk")->group(function (){
    Route::get("get",[VkontakteController::class,"get"])->name("vkontakte.get");
    Route::post("callback",[VkontakteController::class,"callback"]);
})->middleware(\App\Http\Middleware\HandleReferralCode::class);
Route::prefix("auth/yandex")->group(function (){
    Route::get("get",[\Authorization\Http\Controllers\Oauth\YandexController::class,"get"])->name("yandex.get");
    Route::get("callback",[\Authorization\Http\Controllers\Oauth\YandexController::class,"callback"]);
})->middleware(\App\Http\Middleware\HandleReferralCode::class);

Route::post('/email-subscribe', [SubscriptionController::class, 'store'])->name('subscribe.store');

// Для авторизованных (привязка/отвязка соцсетей)
Route::prefix("profile/social")->middleware('auth')->group(function () {
    Route::delete("vk/detach", [VkontakteController::class,"detach"])->name("profile.vk.detach");

    Route::delete("yandex/detach", [YandexController::class,"detach"])->name("profile.yandex.detach");
});


Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,15'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,15')
        ->name('verification.send');

    Route::get('/phone-verify', function () {
        return Inertia::render('back/Auth/VerifyPhone', ['target' => \Authorization\Enums\CodeSendTarget::new_phone_verify->name]);
    })->name('phone-verify');


    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

Route::controller(CodeVerificationController::class)->prefix('code')->name('code.')->group(function () {
    Route::post('send', 'sendCodeToPhone')->name('send')->middleware(['throttle:15, 15']);
    Route::post('confirm', 'confirmCode')->name('confirm')->middleware(['throttle:15, 15']);
});


//смена почты и телефона в профиле
Route::middleware('auth')->prefix('setting')->group(function () {
    Route::post('/change-email', [ChangeEmailController::class, 'store'])
        ->name('email.change.store');

    Route::post('/change-phone/send', [ChangePhoneController::class, 'send'])
        ->name('phone.change.send');
    Route::post('/change-phone/confirm', [ChangePhoneController::class, 'confirm'])
        ->name('phone.change.confirm');
});


// роуты для локального env('AUTH_INERTIA', false)

Route::middleware(\App\Http\Middleware\BlockIfAuthInertiaEnabled::class)->group(function () {

    Route::get('/register-phone', function () {
        return Inertia::render('back/Auth/RegisterByPhone', ['target' => \Authorization\Enums\CodeSendTarget::register->name]);
    })->name('register-phone');
    Route::get('/register-phone-confirm', function () {
        return Inertia::render('back/Auth/RegisterByPhoneConfirm', ['target' => \Authorization\Enums\CodeSendTarget::register->name]);
    })->name('register-phone-confirm');

    Route::get('/login-phone-confirm', function () {
        return Inertia::render('back/Auth/LoginByPhoneConfirm', ['target' => \Authorization\Enums\CodeSendTarget::login->name]);
    })->name('login-phone-confirm');

    Route::get('/phone-confirm', function () {
        return Inertia::render('back/Auth/VerifyPhoneConfirm', ['target' => \Authorization\Enums\CodeSendTarget::new_phone_verify->name]);
    })->name('phone-confirm');

    Route::get('/forgot-password-phone-confirm', function () {
        return Inertia::render('back/Auth/ForgotPasswordByPhoneConfirm', ['target' => \Authorization\Enums\CodeSendTarget::password_reset->name]);
    })->name('forgot-password-phone-confirm');
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::get('/login-phone', function () {
        return Inertia::render('back/Auth/LoginByPhone', ['target' => \Authorization\Enums\CodeSendTarget::login->name]);
    })->name('login-phone');

    Route::get('/forgot-password-phone', function () {
        return Inertia::render('back/Auth/ForgotPasswordByPhone', ['target' => \Authorization\Enums\CodeSendTarget::password_reset->name]);
    })->name('forgot-password-phone');
    Route::get('/reset-password-phone', function () {
        return Inertia::render('back/Auth/ResetPasswordByPhone', ['target' => \Authorization\Enums\CodeSendTarget::password_reset->name, 'code' => request()->code]);
    })->name('reset-password-phone');

});


