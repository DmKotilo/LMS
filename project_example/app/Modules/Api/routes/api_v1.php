<?php

use Api\Http\Controllers\v1\BrandController;
use Api\Http\Controllers\v1\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(\Api\Http\Middleware\ApiAuthKey::class)->group(function () {
/*    Route::prefix("categories")->controller(CategoryController::class)->group(function () {
        Route::post("/exchange", 'exchange');
    });*/

    Route::prefix("brands")->controller(BrandController::class)->group(function () {
        Route::post("/exchange", 'exchange');
    });

    Route::prefix("products")->controller(\Api\Http\Controllers\v1\ProductController::class)->group(function () {
        Route::post("/exchange", 'exchange');
        Route::post("/exchange/quantity-by-city", 'quantity_by_city');
        Route::post("/exchange/prices", 'prices');
    });

    Route::prefix("cities")->controller(\Api\Http\Controllers\v1\CityController::class)->group(function () {
        Route::post("/exchange", 'exchange');
    });

    Route::controller(\Api\Http\Controllers\v1\OrderApiController::class)->group(function () {
        Route::get("/look-order", 'index');
        Route::post("orders/exchange", 'exchange');
    });
});

// API для Bitrix24 с отдельной авторизацией
Route::middleware(\Api\Http\Middleware\Bitrix24AuthKey::class)->prefix('bitrix24')->group(function () {
    Route::controller(\Api\Http\Controllers\v1\Bitrix24Controller::class)->group(function () {
        Route::post('webhook/deal-update', 'updateDealId');
        Route::get('order/by-lead-id', 'getOrderByLeadId');
        Route::get('status', 'status');
    });
});

Route::get("/auth-check",[\Api\Http\Controllers\BaseController::class,'authCheck'])->name('auth-check');

