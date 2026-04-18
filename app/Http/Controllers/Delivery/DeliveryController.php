<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    // ── Today's deliveries ─────────────────────────────────────────

    public function today()
    {
        $staffId = auth()->id();
        $today   = today();

        $deliveries = Delivery::with(['order.customer.zone', 'order.dealer.zone', 'order.items.product', 'zone', 'payments'])
            ->where('delivery_staff_id', $staffId)
            ->where(fn ($q) => $q
                ->whereDate('assigned_at', $today)
                ->orWhereHas('order', fn ($oq) => $oq->whereDate('order_date', $today))
            )
            ->orderByRaw("FIELD(delivery_status, 'in_progress', 'assigned', 'pending', 'failed', 'delivered', 'cancelled')")
            ->get();

        return view('delivery.today', compact('deliveries'));
    }

    // ── All assigned deliveries ────────────────────────────────────

    public function index(Request $request)
    {
        $staffId = auth()->id();

        $deliveries = Delivery::with(['order.customer', 'order.dealer', 'zone'])
            ->where('delivery_staff_id', $staffId)
            ->when($request->status, fn ($q) => $q->where('delivery_status', $request->status))
            ->latest('created_at')
            ->paginate(20);

        return view('delivery.deliveries.index', compact('deliveries'));
    }

    // ── Delivery detail ────────────────────────────────────────────

    public function show(Delivery $delivery)
    {
        $this->authorizeDelivery($delivery);

        $delivery->load(['order.customer.zone', 'order.dealer.zone', 'order.items.product', 'zone', 'payments']);

        return view('delivery.deliveries.show', compact('delivery'));
    }

    // ── Mark In Progress ──────────────────────────────────────────

    public function markInProgress(Delivery $delivery)
    {
        $this->authorizeDelivery($delivery);

        abort_if(
            ! in_array($delivery->delivery_status, ['assigned', 'pending']),
            422,
            'Cannot mark this delivery as in progress.'
        );

        $delivery->markInProgress();

        return back()->with('success', 'Delivery marked as in progress.');
    }

    // ── Mark Delivered ────────────────────────────────────────────

    public function markDelivered(Delivery $delivery)
    {
        $this->authorizeDelivery($delivery);

        abort_if(
            ! in_array($delivery->delivery_status, ['assigned', 'in_progress', 'pending']),
            422,
            'Cannot mark this delivery as delivered.'
        );

        $delivery->markDelivered();

        return redirect()->route('delivery.today')
            ->with('success', "Delivery {$delivery->delivery_no} marked as delivered.");
    }

    // ── Mark Failed ───────────────────────────────────────────────

    public function markFailed(Request $request, Delivery $delivery)
    {
        $this->authorizeDelivery($delivery);

        abort_if(
            ! in_array($delivery->delivery_status, ['assigned', 'in_progress', 'pending']),
            422,
            'Cannot mark this delivery as failed.'
        );

        $request->validate([
            'failure_reason' => ['required', 'string', 'max:500'],
        ]);

        $delivery->markFailed($request->failure_reason);

        return back()->with('success', 'Delivery marked as failed.');
    }

    // ── Bulk Mark Delivered ───────────────────────────────────────

    public function bulkMarkDelivered(Request $request)
    {
        $request->validate([
            'delivery_ids'   => ['required', 'array', 'min:1'],
            'delivery_ids.*' => ['integer', 'exists:deliveries,id'],
        ]);

        $staffId = auth()->id();

        $deliveries = Delivery::whereIn('id', $request->delivery_ids)
            ->where('delivery_staff_id', $staffId)
            ->whereIn('delivery_status', ['assigned', 'in_progress', 'pending'])
            ->get();

        if ($deliveries->isEmpty()) {
            return back()->withErrors(['delivery_ids' => 'No eligible deliveries selected.']);
        }

        DB::transaction(function () use ($deliveries) {
            foreach ($deliveries as $delivery) {
                $delivery->markDelivered();
            }
        });

        return redirect()->route('delivery.today')
            ->with('success', "{$deliveries->count()} delivery(s) marked as delivered.");
    }

    // ── Private helpers ───────────────────────────────────────────

    private function authorizeDelivery(Delivery $delivery): void
    {
        abort_if($delivery->delivery_staff_id !== auth()->id(), 403, 'Access denied.');
    }
}
