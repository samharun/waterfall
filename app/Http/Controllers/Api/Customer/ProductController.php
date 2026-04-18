<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Customer\Concerns\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $today    = now()->toDateString();

        $products = Product::active()
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($customer, $today) {
                $customerPrice = (float) $product->getPriceForCustomer($customer->id, $today);

                return [
                    'id'             => $product->id,
                    'name'           => $product->name,
                    'sku'            => $product->sku,
                    'type'           => $product->product_type,
                    'default_price'  => (float) $product->default_price,
                    'customer_price' => $customerPrice,
                    'deposit_amount' => (float) $product->deposit_amount,
                    'in_stock'       => $product->current_stock > 0,
                    'current_stock'  => $product->current_stock,
                ];
            });

        return $this->success('Products retrieved.', ['products' => $products]);
    }
}
