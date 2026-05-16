<?php

//TODO: Тест
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;
use Product\Models\Product;

Route::controller(FileController::class)->prefix('upload')->group(function () {
    Route::post('uploadFromData', 'buildFileObjectFromFormData');
    Route::post('uploadFromBase64', 'buildFileObjectFromBase64');
});

Route::controller(FileController::class)->prefix('sort')->group(function () {
    Route::post('reorder-images/{encodedModel}/{id}', 'reorderImages');
    Route::post('reorder-videos/{encodedModel}/{id}', 'reorderVideos');
//    Route::post('video/{videoFile}/preview', 'storeVideoPreview');
})->middleware("moonshine");





