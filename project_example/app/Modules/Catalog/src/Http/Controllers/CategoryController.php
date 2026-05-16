<?php

namespace Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Catalog\Http\Resources\ProductCardResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Product\Models\Product;

class CategoryController extends Controller
{
    public function index()
    {

        $products = Product::all();

        dd(ProductCardResource::collection($products));

        return Inertia::render('catalog/ui/CatalogPage', [
            'products' => ProductCardResource::collection($products),
        ]);
    }
}
