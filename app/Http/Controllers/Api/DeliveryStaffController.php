<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\SendFirebaseNotificationJob;
use App\Models\Delivery;
use App\Models\JarDeposit;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeliveryStaffController extends Controller
{
    use ApiResponse;

    private const MOBILE_STATUSES = [
        'pending',
        'delivered',
        'partial_delivered',
        'not_delivered',
        'customer_unavailable',
        'cancelled',
    ];

    public function dashboard(Request $request): JsonResponse
    {
        if (! $this->isDeliveryStaff($request->user())) {
            return $this->forbiddenResponse();
        }

        $base = $this->todayDeliveriesQuery($request->user()->id);

        $deliveredStatuses = ['delivered', 'partial_delivered'];
        $pendingStatuses = ['pending', 'assigned', 'in_progress'];

        return $this->successResponse('Dashboard loaded successfully.', [
            'today_assigned' => (clone $base)->count(),
            'delivered' => (clone $base)->whereIn('delivery_status', $deliveredStatuses)->count(),
            'pending' => (clone $base)->whereIn('delivery_status', $pendingStatuses)->count(),
            'collection' => (float) $this->todayPaymentsQuery($request->user()->id)->sum('amount'),
            'total_due' => (float) (clone $base)->with('order.payments')->get()->sum(fn (Delivery $delivery) => $this->dueAmount($delivery)),
            'total_jars' => (int) (clone $base)->with('order.items')->get()->sum(fn (Delivery $delivery) => $this->jarQuantity($delivery)),
            'empty_jars_returned' => (int) $this->todayJarReturnsQuery($request->user()->id)->sum('quantity'),
        ]);
    }

    public function todayDeliveries(Request $request): JsonResponse
    {
        if (! $this->isDeliveryStaff($request->user())) {
            return $this->forbiddenResponse();
        }

        $deliveries = $this->todayDeliveriesQuery($request->user()->id)
            ->with($this->deliveryRelations())
            ->orderByRaw("FIELD(delivery_status, 'in_progress', 'assigned', 'pending', 'partial_delivered', 'not_delivered', 'customer_unavailable', 'delivered', 'cancelled', 'failed')")
            ->get()
            ->map(fn (Delivery $delivery) => $this->deliveryPayload($delivery))
            ->values()
            ->all();

        return $this->successResponse('Today deliveries loaded successfully.', $deliveries);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        if (! $this->isDeliveryStaff($request->user())) {
            return $this->forbiddenResponse();
        }

        $validator = Validator::make($request->all(), [
            'delivery_id' => ['required', 'integer', 'exists:deliveries,id'],
            'status' => ['required', Rule::in(self::MOBILE_STATUSES)],
            'delivered_jar_quantity' => ['nullable', 'integer', 'min:0'],
            'empty_jar_return' => ['nullable', 'integer', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['required_if:status,not_delivered,customer_unavailable', 'nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $delivery = Delivery::with($this->deliveryRelations())->find($data['delivery_id']);

        if (! $delivery || (int) $delivery->delivery_staff_id !== (int) $request->user()->id) {
            return $this->forbiddenResponse();
        }

        try {
            DB::transaction(function () use ($delivery, $data, $request): void {
                $delivery->update([
                    'delivery_status' => $data['status'],
                    'delivered_at' => $data['status'] === 'delivered' ? now() : $delivery->delivered_at,
                    'delivery_note' => $data['remarks'] ?? $delivery->delivery_note,
                    'failure_reason' => in_array($data['status'], ['not_delivered', 'customer_unavailable'], true)
                        ? ($data['remarks'] ?? null)
                        : $delivery->failure_reason,
                ]);

                $this->recordPaymentIfNeeded($delivery, (float) ($data['paid_amount'] ?? 0), $request->user()->id, $data['remarks'] ?? null);
                $this->recordEmptyJarReturnIfNeeded($delivery, (int) ($data['empty_jar_return'] ?? 0), $request->user()->id, $data['remarks'] ?? null);
                // TODO: Persist delivered_jar_quantity if a dedicated column/table is added later.
            });
        } catch (\Throwable $e) {
            Log::error('Delivery staff status update failed.', ['delivery_id' => $delivery->id, 'error' => $e->getMessage()]);

            return $this->errorResponse('Server error. Please try again later.', 500);
        }

        $delivery->refresh()->load($this->deliveryRelations());
        $this->notifyManagerStatusUpdated($delivery, $data['status']);

        return $this->successResponse('Delivery status updated successfully.', [
            'delivery' => $this->deliveryPayload($delivery),
        ]);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        if (! $this->isDeliveryStaff($request->user())) {
            return $this->forbiddenResponse();
        }

        $validator = Validator::make($request->all(), [
            'delivery_ids' => ['required', 'array', 'min:1'],
            'delivery_ids.*' => ['integer', 'exists:deliveries,id'],
            'status' => ['required', Rule::in(self::MOBILE_STATUSES)],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $data = $validator->validated();

        try {
            $updatedCount = DB::transaction(function () use ($data, $request): int {
                $deliveries = Delivery::query()
                    ->whereIn('id', $data['delivery_ids'])
                    ->where('delivery_staff_id', $request->user()->id)
                    ->get();

                foreach ($deliveries as $delivery) {
                    $delivery->update([
                        'delivery_status' => $data['status'],
                        'delivered_at' => $data['status'] === 'delivered' ? now() : $delivery->delivered_at,
                        'delivery_note' => $data['remarks'] ?? $delivery->delivery_note,
                    ]);
                }

                return $deliveries->count();
            });
        } catch (\Throwable $e) {
            Log::error('Delivery staff bulk update failed.', ['error' => $e->getMessage()]);

            return $this->errorResponse('Server error. Please try again later.', 500);
        }

        return $this->successResponse('Bulk delivery update completed successfully.', [
            'updated_count' => $updatedCount,
        ]);
    }

    private function todayDeliveriesQuery(int $staffId): Builder
    {
        $today = today();

        return Delivery::query()
            ->where('delivery_staff_id', $staffId)
            ->where(fn (Builder $query) => $query
                ->whereDate('assigned_at', $today)
                ->orWhereHas('order', fn (Builder $orderQuery) => $orderQuery->whereDate('order_date', $today))
            );
    }

    private function todayPaymentsQuery(int $staffId): Builder
    {
        return Payment::query()
            ->where('received_by', $staffId)
            ->where('collection_source', 'delivery_staff')
            ->where('collection_status', 'accepted')
            ->whereDate('payment_date', today());
    }

    private function todayJarReturnsQuery(int $staffId): Builder
    {
        return JarDeposit::query()
            ->where('transaction_type', 'jar_returned')
            ->where('created_by', $staffId)
            ->whereDate('transaction_date', today());
    }

    private function deliveryRelations(): array
    {
        return [
            'order.customer.zone',
            'order.dealer.zone',
            'order.items.product',
            'order.payments',
            'zone',
            'payments',
            'deliveryStaff',
        ];
    }

    private function deliveryPayload(Delivery $delivery): array
    {
        $order = $delivery->order;
        $party = $order?->customer ?? $order?->dealer;
        $partyCode = $order?->order_type === 'dealer' ? $party?->dealer_code : $party?->customer_id;

        return [
            'id' => $delivery->id,
            'order_no' => $order?->order_no,
            'customer_id' => $partyCode,
            'customer_name' => $party?->name,
            'mobile' => $party?->mobile,
            'address' => $party?->address,
            'zone_name' => $delivery->zone?->name ?? $party?->zone?->name,
            'line_name' => null,
            'jar_quantity' => $this->jarQuantity($delivery),
            'empty_jar_return' => 0,
            'payable_amount' => (float) ($order?->total_amount ?? 0),
            'paid_amount' => (float) $delivery->payments->sum('amount'),
            'due_amount' => $this->dueAmount($delivery),
            'preferred_time' => $this->preferredTime($delivery),
            'status' => $this->mobileStatus($delivery->delivery_status),
            'assigned_staff_id' => $delivery->delivery_staff_id,
            'assigned_staff_name' => $delivery->deliveryStaff?->name,
            'remarks' => $delivery->failure_reason ?? $delivery->delivery_note,
        ];
    }

    private function jarQuantity(Delivery $delivery): int
    {
        return (int) ($delivery->order?->items?->sum('quantity') ?? 0);
    }

    private function dueAmount(Delivery $delivery): float
    {
        $total = (float) ($delivery->order?->total_amount ?? 0);
        $paid = (float) $delivery->payments->sum('amount');

        return max(0, $total - $paid);
    }

    private function preferredTime(Delivery $delivery): ?string
    {
        $order = $delivery->order;

        if (! $order) {
            return null;
        }

        if ($order->preferred_delivery_slot === 'custom' && $order->preferred_delivery_time) {
            return $order->preferred_delivery_time->format('Y-m-d H:i:s');
        }

        return ucfirst((string) $order->preferred_delivery_slot);
    }

    private function mobileStatus(?string $status): string
    {
        return match ($status) {
            'assigned', 'in_progress' => 'pending',
            'failed' => 'not_delivered',
            default => in_array($status, self::MOBILE_STATUSES, true) ? $status : 'pending',
        };
    }

    private function recordPaymentIfNeeded(Delivery $delivery, float $amount, int $staffId, ?string $remarks): void
    {
        if ($amount <= 0) {
            return;
        }

        $order = $delivery->order;

        Payment::create([
            'payment_type' => $order?->order_type ?? 'customer',
            'customer_id' => $order?->customer_id,
            'dealer_id' => $order?->dealer_id,
            'order_id' => $order?->id,
            'delivery_id' => $delivery->id,
            'payment_date' => today()->toDateString(),
            'amount' => $amount,
            'payment_method' => 'cash',
            'received_by' => $staffId,
            'collection_source' => 'delivery_staff',
            'collection_status' => 'accepted',
            'collected_at' => now(),
            'remarks' => $remarks,
        ]);
    }

    private function recordEmptyJarReturnIfNeeded(Delivery $delivery, int $quantity, int $staffId, ?string $remarks): void
    {
        if ($quantity <= 0) {
            return;
        }

        $order = $delivery->order;
        $product = $order?->items?->first(fn ($item) => $item->product?->product_type === 'jar')?->product
            ?? Product::query()->where('product_type', 'jar')->where('status', 'active')->first();

        if (! $order || ! $product) {
            // TODO: Store empty jar returns once a dedicated delivery jar-return table exists.
            return;
        }

        JarDeposit::create([
            'party_type' => $order->order_type === 'dealer' ? 'dealer' : 'customer',
            'customer_id' => $order->customer_id,
            'dealer_id' => $order->dealer_id,
            'product_id' => $product->id,
            'transaction_type' => 'jar_returned',
            'quantity' => $quantity,
            'deposit_amount' => 0,
            'transaction_date' => today()->toDateString(),
            'remarks' => $remarks,
            'created_by' => $staffId,
        ]);
    }

    private function notifyManagerStatusUpdated(Delivery $delivery, string $status): void
    {
        try {
            $manager = $delivery->zone?->deliveryManager;

            if (! $manager || $manager->role !== 'delivery_manager') {
                return;
            }

            $orderNo = (string) ($delivery->order?->order_no ?? ('#'.$delivery->order_id));
            $statusLabel = str($status)->replace('_', ' ')->title()->toString();

            SendFirebaseNotificationJob::dispatch(
                [$manager->id],
                'Delivery Status Updated',
                sprintf('Order %s is now %s.', $orderNo, $statusLabel),
                [
                    'type' => 'delivery_status_updated',
                    'delivery_id' => (string) $delivery->id,
                    'order_no' => $orderNo,
                    'screen' => 'manager_today_deliveries',
                ],
            )->afterCommit();
        } catch (\Throwable $exception) {
            Log::warning('Failed to queue delivery status push notification.', [
                'delivery_id' => $delivery->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function isDeliveryStaff(mixed $user): bool
    {
        return $user instanceof User && $user->role === 'delivery_staff';
    }
}
