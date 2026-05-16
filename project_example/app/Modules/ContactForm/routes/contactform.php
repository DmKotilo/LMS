<?php

//TODO: Тест
use ContactForm\Http\Controllers\CallContactFormController;
use File\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;


Route::post('/call-contact', [CallContactFormController::class, 'store'])->name('call_contact.store');
