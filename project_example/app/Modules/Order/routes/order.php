<?php

use Illuminate\Support\Facades\Route;
use Order\Http\Controllers\OrderController;
use Order\Http\Controllers\Bitrix24WebhookController;

Route::controller(OrderController::class)->group(function () {
    Route::get('create', 'create')->name('create');
    Route::get('calculate', 'calculateOrder')->name('calculate');
    Route::get('store/get', 'store_get')->name('store_get');
    Route::post('store', 'store')->name('store');
    Route::get('pickup-location-by-city/get', 'getPickupLocationByCity')->name('pickup-location-by-city.get');
    Route::post('promo-check/{promo}', 'promo_check')->name('promo.check');
});

// API маршруты для интеграции с Битрикс24
Route::prefix('bitrix24')->name('bitrix24.')->group(function () {
    Route::post('webhook/deal-update', [Bitrix24WebhookController::class, 'updateDealId'])
        ->name('webhook.deal-update');
    Route::get('order/by-lead-id', [Bitrix24WebhookController::class, 'getOrderByLeadId'])
        ->name('order.by-lead-id');
    Route::get('status', [Bitrix24WebhookController::class, 'status'])
        ->name('status');
});
