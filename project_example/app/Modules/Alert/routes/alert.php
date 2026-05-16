<?php

use Illuminate\Support\Facades\Route;
use Alert\Http\Controllers\AlertController;


Route::post('/mark-as-read', [AlertController::class, 'markAsRead'])->name('mark-as-read');
//Route::delete('/delete/{id}', [AlertController::class, 'markAsDeleted'])->name('mark-as-deleted');
Route::delete('/delete', [AlertController::class, 'markAsDeleted'])->name('mark-as-deleted');
