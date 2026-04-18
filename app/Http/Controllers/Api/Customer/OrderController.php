<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Customer\Concerns\ApiResponse;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    use ApiResponse;

    // ── List orders ────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $locale   = $this->resolveLocale($request);
        $customer = $request->user();

        $orders = Order::forCustomer($customer->id)
            ->with('delivery')
            ->latest()
            ->paginate(15);

        $items = $orders->getCollection()->map(fn (Order $o) => [
            'id'                   => $o->id,
            'order_no'             => $o->order_no,
            'order_date'           => $o->order_date?->toDateString(),
            'delivery_slot'        => $o->preferred_delivery_slot,
            'delivery_slot_label'  => $this->translateSlot($o->preferred_delivery_slot, $locale),
            'total_amount'         => (float) $o->total_amount,
            'payment_status'       => $o->payment_status,
            'payment_status_label' => $this->translateStatus($o->payment_status, $locale),
            'order_status'         => $o->order_status,
            'order_status_label'   => $this->translateStatus($o->order_status, $locale),
            'delivery_status'      => $o->delivery?->delivery_status,
            'delivery_status_label' => $o->delivery
                ? $this->translateStatus($o->delivery->delivery_status, $locale)
                : null,
        ]);

        return $this->success('Orders retrieved.', [
            'orders'     => $items,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    // ── Show single order ──────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $locale   = $this->resolveLocale($request);
        $customer = $request->user();

        $order = Order::forCustomer($customer->id)
            ->with(['items.product', 'delivery', 'zone'])
            ->find($id);

        if (! $order) {
            return $this->error('Order not found.', 404);
        }

        return $this->success('Order retrieved.', [
            'id'                      => $order->id,
            'order_no'                => $order->order_no,
            'order_date'              => $order->order_date?->toDateString(),
            'delivery_slot'           => $order->preferred_delivery_slot,
            'delivery_slot_label'     => $this->translateSlot($order->preferred_delivery_slot, $locale),
            'preferred_delivery_time' => $order->preferred_delivery_time?->toDateTimeString(),
            'subtotal'                => (float) $order->subtotal,
            'discount'                => (float) $order->discount,
            'delivery_charge'         => (float) $order->delivery_charge,
            'total_amount'            => (float) $order->total_amount,
            'payment_status'          => $order->payment_status,
            'payment_status_label'    => $this->translateStatus($order->payment_status, $locale),
            'order_status'            => $order->order_status,
            'order_status_label'      => $this->translateStatus($order->order_status, $locale),
            'remarks'                 => $order->remarks,
            'zone'                    => $order->zone?->name,
            'items' => $order->items->map(fn (OrderItem $item) => [
                'product_id'   => $item->product_id,
                'product_name' => $item->product?->name,
                'sku'          => $item->product?->sku,
                'quantity'     => $item->quantity,
                'unit_price'   => (float) $item->unit_price,
                'line_total'   => (float) $item->line_total,
            ]),
            'delivery' => $order->delivery ? [
                'delivery_no'          => $order->delivery->delivery_no,
                'status'               => $order->delivery->delivery_status,
                'status_label'         => $this->translateStatus($order->delivery->delivery_status, $locale),
                'assigned_at'          => $order->delivery->assigned_at?->toDateTimeString(),
                'delivered_at'         => $order->delivery->delivered_at?->toDateTimeString(),
            ] : null,
        ]);
    }

    // ── Place order ────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $customer = $request->user();

        if ($customer->approval_status !== 'approved') {
            return $this->error('Your account is not active. Please wait for admin approval.', 403);
        }

        try {
            $data = $request->validate([
                'product_id'              => 'required|exists:products,id',
                'quantity'                => 'required|integer|min:1',
                'delivery_slot'           => 'required|in:now,morning,afternoon,evening,custom',
                'preferred_delivery_time' => 'required_if:delivery_slot,custom|nullable|date',
                'note'                    => 'nullable|string|max:500',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $product = Product::active()->find($data['product_id']);

        if (! $product) {
            return $this->error('Product not available.', 400);
        }

        $today     = now()->toDateString();
        $unitPrice = (float) $product->getPriceForCustomer($customer->id, $today);
        $lineTotal = round($unitPrice * $data['quantity'], 2);

        try {
            $order = DB::transaction(function () use ($customer, $data, $product, $unitPrice, $lineTotal) {
                $order = Order::create([
                    'order_type'              => 'customer',
                    'customer_id'             => $customer->id,
                    'zone_id'                 => $customer->zone_id,
                    'preferred_delivery_slot' => $data['delivery_slot'],
                    'preferred_delivery_time' => $data['preferred_delivery_time'] ?? null,
                    'order_date'              => now()->toDateString(),
                    'subtotal'                => $lineTotal,
                    'discount'                => 0,
                    'delivery_charge'         => 0,
                    'total_amount'            => $lineTotal,
                    'payment_status'          => 'unpaid',
                    'order_status'            => 'pending',
                    'remarks'                 => $data['note'] ?? null,
                ]);

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $data['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                return $order;
            });
        } catch (\Throwable) {
            return $this->error('Failed to place order. Please try again.', 500);
        }

        $locale = $this->resolveLocale($request);

        return $this->success('Order placed successfully.', [
            'order_no'           => $order->order_no,
            'order_status'       => $order->order_status,
            'order_status_label' => $this->translateStatus($order->order_status, $locale),
            'total_amount'       => (float) $order->total_amount,
            'order_date'         => $order->order_date?->toDateString(),
        ], 201);
    }
}
