<?php

use Connect\Http\Controllers\CheckConnectController;
use Illuminate\Support\Facades\Route;

Route::post('connect/check', [CheckConnectController::class, 'check']);
