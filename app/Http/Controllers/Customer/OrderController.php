<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $customer = auth()->user()->customer;

        $orders = $customer->orders()
            ->with(['items.product', 'delivery'])
            ->latest('order_date')
            ->paginate(15);

        return view('customer.orders.index', compact('customer', 'orders'));
    }

    public function create()
    {
        $products = Product::active()->orderBy('name')->get();
        $slots    = Order::deliverySlotLabels();

        return view('customer.orders.create', compact('products', 'slots'));
    }

    public function store(Request $request)
    {
        $customer = auth()->user()->customer;

        $validated = $request->validate([
            'product_id'              => ['required', 'exists:products,id'],
            'quantity'                => ['required', 'integer', 'min:1', 'max:100'],
            'preferred_delivery_slot' => ['required', 'in:now,morning,afternoon,evening,custom'],
            'preferred_delivery_time' => ['required_if:preferred_delivery_slot,custom', 'nullable', 'date'],
            'remarks'                 => ['nullable', 'string', 'max:500'],
        ]);

        $product   = Product::active()->findOrFail($validated['product_id']);
        $unitPrice = (float) $product->getPriceForCustomer($customer->id, today()->toDateString());
        $qty       = (int) $validated['quantity'];
        $lineTotal = $qty * $unitPrice;

        $order = Order::create([
            'order_no'               => Order::generateOrderNo(),
            'order_type'             => 'customer',
            'customer_id'            => $customer->id,
            'zone_id'                => $customer->zone_id,
            'ordered_by'             => auth()->id(),
            'preferred_delivery_slot'=> $validated['preferred_delivery_slot'],
            'preferred_delivery_time'=> $validated['preferred_delivery_time'] ?? null,
            'order_date'             => today()->toDateString(),
            'subtotal'               => $lineTotal,
            'discount'               => 0,
            'delivery_charge'        => 0,
            'total_amount'           => $lineTotal,
            'payment_status'         => 'unpaid',
            'order_status'           => 'pending',
            'remarks'                => $validated['remarks'] ?? null,
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => $qty,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ]);

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Your order has been placed successfully.');
    }

    public function show(Order $order)
    {
        $customer = auth()->user()->customer;

        abort_if($order->customer_id !== $customer->id, 403);

        $order->load(['items.product', 'delivery', 'zone']);

        return view('customer.orders.show', compact('order'));
    }
}
