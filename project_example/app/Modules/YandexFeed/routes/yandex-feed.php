<?php

use Illuminate\Support\Facades\Route;
use YandexFeed\Http\Controllers\YandexFeedController;

Route::get('/{fileName}', [YandexFeedController::class, 'show'])
    ->name('show');

