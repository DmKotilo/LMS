<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MainPageController extends Controller
{
    public function show() {
        return Inertia::render('main-page/ui/MainPage');
    }
}
