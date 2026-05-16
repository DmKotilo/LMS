<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PromoPageController extends Controller
{
    public function show() {
        return Inertia::render('promo/ui/PromoPage');
    }
}
