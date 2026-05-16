<?php

use App\Modules\City\src\Http\Controllers\UserCityController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Review\Http\Controllers\ReviewController;
use User\Http\Controllers\ProfileGlobalController;
use User\Http\Controllers\SubscriptionController;
use User\Models\User;

Route::controller(ProfileGlobalController::class)->name('profile.')->prefix('profile')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/privilege-program', 'privilegeProgram')->name('privilege.program');
    Route::get('/privilege-program/bonus-history', 'bonusHistory')->name('bonus.history');
    Route::get('/notifications', 'notifications')->name('notifications');
    Route::get('/delete-page', 'deletePage')->name('delete.page');
    Route::post('/delete', 'delete')->name('delete');
    Route::post('/password-change', 'passwordChange')->name('password.change');
    Route::put('/update', 'update')->name('update');
    Route::get('/logout', 'logout')->name('logout');
    Route::get('/orders-history', 'ordersHistory')->name('orders_history');
    Route::get('/orders-history/{order}', 'ordersHistoryShow')->name('orders.history.show');
    Route::get('/white-list', 'whiteList')->name('white_list');
    Route::get('/reviews-page', 'reviews')->name('reviews');
    Route::get('/edit', 'edit')->name('edit');
    Route::get('/settings', 'settings')->name('settings');
    Route::put('/settings/update', 'settingsUpdate')->name('settings.update');
});
Route::controller(ReviewController::class)->prefix('profile/reviews')->name('profile.reviews.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::post('/{review}', 'update')->name('update');
        Route::delete('/{review}', 'destroy')->name('destroy');
    });

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return User::getRedirectProfileByRole()->with(['success' => 'Email успешно подтверждён']);
})->middleware(['auth:web', 'signed'])->name('verification.verify')->withoutMiddleware('auth:web');
