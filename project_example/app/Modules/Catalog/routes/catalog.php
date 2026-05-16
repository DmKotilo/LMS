<?php

use App\Modules\Catalog\src\Http\Controllers\ProductDetailController;
use Catalog\Http\Controllers\CatalogController;
use Catalog\Http\Controllers\ProductSearchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*Route::get('/catalog', function () {
    return Inertia::render('catalog/ui/CatalogPage');
})->middleware(['auth', 'verified'])->name('catalog-page');*/

Route::controller(ProductDetailController::class)->group(function () {
//    Route::get('product/{id}', 'show')->name('product.show');
    Route::get('product/{product:slug}', 'show')->name('product.show');
});
Route::controller(ProductSearchController::class)->group(function () {
    Route::get('/autocomplete', 'autocomplete')->name('search.autocomplete');
});
Route::controller(CatalogController::class)->prefix('')->name('')->group(function () {
    Route::get('{category?}', 'index')->where('category', '.*')->name('index');
});




