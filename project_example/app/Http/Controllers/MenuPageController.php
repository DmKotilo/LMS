<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuPageController extends Controller
{
    public function show()
    {
        return Inertia::render('menu/ui/MenuPage');
    }
}
