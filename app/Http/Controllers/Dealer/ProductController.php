<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $dealer   = auth()->user()->dealer;
        $products = Product::active()->orderBy('name')->get();
        $today    = today()->toDateString();

        $priceList = $products->map(fn (Product $p) => [
            'product'      => $p,
            'default_price'=> (float) $p->default_price,
            'dealer_price' => (float) $p->getPriceForDealer($dealer->id, $today),
            'has_custom'   => (float) $p->getPriceForDealer($dealer->id, $today) !== (float) $p->default_price,
        ]);

        return view('dealer.products.index', compact('priceList'));
    }
}
