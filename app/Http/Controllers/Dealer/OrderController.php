<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $dealer = auth()->user()->dealer;

        $orders = $dealer->orders()
            ->with(['items.product', 'delivery'])
            ->latest('order_date')
            ->paginate(15);

        return view('dealer.orders.index', compact('dealer', 'orders'));
    }

    public function create()
    {
        $dealer   = auth()->user()->dealer;
        $products = Product::active()->orderBy('name')->get();
        $slots    = Order::deliverySlotLabels();
        $today    = today()->toDateString();

        return view('dealer.orders.create', compact('dealer', 'products', 'slots', 'today'));
    }

    public function store(Request $request)
    {
        $dealer = auth()->user()->dealer;

        $validated = $request->validate([
            'product_id'              => ['required', 'exists:products,id'],
            'quantity'                => ['required', 'integer', 'min:1', 'max:500'],
            'preferred_delivery_slot' => ['required', 'in:now,morning,afternoon,evening,custom'],
            'preferred_delivery_time' => ['required_if:preferred_delivery_slot,custom', 'nullable', 'date'],
            'remarks'                 => ['nullable', 'string', 'max:500'],
        ]);

        $product   = Product::active()->findOrFail($validated['product_id']);
        $unitPrice = (float) $product->getPriceForDealer($dealer->id, today()->toDateString());
        $qty       = (int) $validated['quantity'];
        $lineTotal = $qty * $unitPrice;

        $order = Order::create([
            'order_no'               => Order::generateOrderNo(),
            'order_type'             => 'dealer',
            'dealer_id'              => $dealer->id,
            'zone_id'                => $dealer->zone_id,
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

        return redirect()->route('dealer.orders.show', $order)
            ->with('success', 'Your dealer order has been placed successfully.');
    }

    public function show(Order $order)
    {
        $dealer = auth()->user()->dealer;

        abort_if($order->dealer_id !== $dealer->id, 403);

        $order->load(['items.product', 'delivery', 'zone']);

        return view('dealer.orders.show', compact('order'));
    }
}
