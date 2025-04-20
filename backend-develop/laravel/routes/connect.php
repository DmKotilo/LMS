<?php

use App\Http\Controllers\Connect\CheckConnectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post("/connect/check",[CheckConnectController::class,"check"]);
