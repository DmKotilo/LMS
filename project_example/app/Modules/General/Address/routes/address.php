<?php


use Address\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;


/*Route::controller(AddressController::class)->group(function (){
    Route::get("find","find");
    Route::get("geolocation","reverseGeocoding");
});*/
Route::post('/address-create', [AddressController::class, 'store'])->name('store');
Route::post('/address-create-api', [AddressController::class, 'storeApi'])->name('store.api');
