<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentCollectionController extends Controller
{
    private function authorizeDelivery(Delivery $delivery): void
    {
        abort_if($delivery->delivery_staff_id !== Auth::id(), 403, 'Access denied.');
        abort_if($delivery->delivery_status === 'cancelled', 422, 'Cannot collect payment for a cancelled delivery.');
    }

    // ── Show collection form ───────────────────────────────────────

    public function create(Delivery $delivery)
    {
        $this->authorizeDelivery($delivery);

        $delivery->load(['order.customer.zone', 'order.dealer.zone', 'order.items.product', 'payments']);

        $order  = $delivery->order;
        $party  = $order?->customer ?? $order?->dealer;

        // Find best invoice to suggest
        $suggestedInvoice = $this->findSuggestedInvoice($order);

        // Available invoices for selection
        $invoices = $this->getAvailableInvoices($order);

        // Suggested amount
        $suggestedAmount = $suggestedInvoice
            ? (float) $suggestedInvoice->due_amount
            : max((float) ($party?->current_due ?? 0), (float) ($order?->total_amount ?? 0));

        $totalCollected = $delivery->totalCollectedAmount();

        return view('delivery.deliveries.collect-payment', compact(
            'delivery', 'order', 'party', 'suggestedInvoice', 'invoices',
            'suggestedAmount', 'totalCollected'
        ));
    }

    // ── Store collection ───────────────────────────────────────────

    public function store(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($delivery);

        $order = $delivery->order;
        $party = $order?->customer ?? $order?->dealer;

        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bkash,nagad,bank,card,other'],
            'invoice_id'     => ['nullable', 'exists:invoices,id'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
            'remarks'        => ['nullable', 'string', 'max:500'],
        ]);

        // Validate invoice ownership and overpayment
        if ($validated['invoice_id']) {
            $invoice = Invoice::find($validated['invoice_id']);

            $partyMatch = $order?->order_type === 'customer'
                ? $invoice?->customer_id === $order?->customer_id
                : $invoice?->dealer_id === $order?->dealer_id;

            if (! $partyMatch) {
                return back()->withErrors(['invoice_id' => 'Selected invoice does not belong to this customer/dealer.']);
            }

            if ((float) $validated['amount'] > (float) $invoice->due_amount) {
                return back()->withErrors(['amount' => "Amount exceeds invoice due amount (৳{$invoice->due_amount}). Please reduce the amount or leave invoice unselected."])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $delivery, $order) {
            Payment::create([
                'payment_type'      => $order?->order_type,
                'customer_id'       => $order?->customer_id,
                'dealer_id'         => $order?->dealer_id,
                'invoice_id'        => $validated['invoice_id'] ?? null,
                'order_id'          => $order?->id,
                'delivery_id'       => $delivery->id,
                'payment_date'      => today()->toDateString(),
                'amount'            => $validated['amount'],
                'payment_method'    => $validated['payment_method'],
                'reference_no'      => $validated['reference_no'] ?? null,
                'received_by'       => Auth::id(),
                'collection_source' => 'delivery_staff',
                'collection_status' => 'accepted',
                'collected_at'      => now(),
                'remarks'           => $validated['remarks'] ?? null,
            ]);
            // Payment model boot auto-syncs invoice + party due
        });

        return redirect()->route('delivery.deliveries.show', $delivery)
            ->with('success', 'Payment of ৳' . number_format((float) $validated['amount'], 2) . ' collected successfully.');
    }

    // ── Mark delivered with payment ────────────────────────────────

    public function markDeliveredWithPayment(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($delivery);

        abort_if(
            ! in_array($delivery->delivery_status, ['assigned', 'in_progress', 'pending']),
            422,
            'Cannot mark this delivery as delivered.'
        );

        $order = $delivery->order;

        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bkash,nagad,bank,card,other'],
            'invoice_id'     => ['nullable', 'exists:invoices,id'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
            'remarks'        => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['invoice_id']) {
            $invoice = Invoice::find($validated['invoice_id']);
            if ((float) $validated['amount'] > (float) $invoice->due_amount) {
                return back()->withErrors(['amount' => "Amount exceeds invoice due (৳{$invoice->due_amount})."])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $delivery, $order) {
            // Record payment
            Payment::create([
                'payment_type'      => $order?->order_type,
                'customer_id'       => $order?->customer_id,
                'dealer_id'         => $order?->dealer_id,
                'invoice_id'        => $validated['invoice_id'] ?? null,
                'order_id'          => $order?->id,
                'delivery_id'       => $delivery->id,
                'payment_date'      => today()->toDateString(),
                'amount'            => $validated['amount'],
                'payment_method'    => $validated['payment_method'],
                'reference_no'      => $validated['reference_no'] ?? null,
                'received_by'       => Auth::id(),
                'collection_source' => 'delivery_staff',
                'collection_status' => 'accepted',
                'collected_at'      => now(),
                'remarks'           => $validated['remarks'] ?? null,
            ]);

            // Mark delivery delivered
            $delivery->markDelivered();
        });

        return redirect()->route('delivery.today')
            ->with('success', "Delivery {$delivery->delivery_no} marked as delivered with payment collected.");
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function findSuggestedInvoice($order): ?Invoice
    {
        if (! $order) {
            return null;
        }

        $query = Invoice::whereIn('invoice_status', ['issued', 'partial'])
            ->where('due_amount', '>', 0);

        if ($order->order_type === 'customer') {
            $query->where('invoice_type', 'customer')->where('customer_id', $order->customer_id);
        } else {
            $query->where('invoice_type', 'dealer')->where('dealer_id', $order->dealer_id);
        }

        return $query->orderBy('invoice_date')->first();
    }

    private function getAvailableInvoices($order): \Illuminate\Support\Collection
    {
        if (! $order) {
            return collect();
        }

        $query = Invoice::whereIn('invoice_status', ['issued', 'partial'])
            ->where('due_amount', '>', 0);

        if ($order->order_type === 'customer') {
            $query->where('invoice_type', 'customer')->where('customer_id', $order->customer_id);
        } else {
            $query->where('invoice_type', 'dealer')->where('dealer_id', $order->dealer_id);
        }

        return $query->orderBy('invoice_date')->get();
    }
}
