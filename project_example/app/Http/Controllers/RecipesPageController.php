<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class RecipesPageController extends Controller
{
    public function show()
    {
        return Inertia::render('recipes/ui/RecipesPage');
    }
}
