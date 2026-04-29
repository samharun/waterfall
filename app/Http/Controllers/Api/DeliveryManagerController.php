<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\SendFirebaseNotificationJob;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeliveryManagerController extends Controller
{
    use ApiResponse;

    public function dashboard(Request $request): JsonResponse
    {
        if (! $this->isDeliveryManager($request->user())) {
            return $this->forbiddenResponse();
        }

        $base = $this->todayDeliveriesQuery($request->user())->with(['order.items', 'payments']);

        $deliveries = (clone $base)->get();

        return $this->successResponse('Manager dashboard loaded successfully.', [
            'total_orders' => $deliveries->count(),
            'delivered' => $deliveries->whereIn('delivery_status', ['delivered', 'partial_delivered'])->count(),
            'pending' => $deliveries->whereIn('delivery_status', ['pending', 'assigned', 'in_progress'])->count(),
            'active_staff' => (clone $base)->whereNotNull('delivery_staff_id')->distinct('delivery_staff_id')->count('delivery_staff_id'),
            'collection' => (float) $this->managerPaymentsQuery($request->user())->sum('amount'),
            'total_due' => (float) $deliveries->sum(fn (Delivery $delivery) => $this->dueAmount($delivery)),
            'jars_delivered' => (int) $deliveries->whereIn('delivery_status', ['delivered', 'partial_delivered'])->sum(fn (Delivery $delivery) => $this->jarQuantity($delivery)),
            'empty_jars_returned' => 0,
        ]);
    }

    public function staffProgress(Request $request): JsonResponse
    {
        if (! $this->isDeliveryManager($request->user())) {
            return $this->forbiddenResponse();
        }

        $zoneIds = $this->managedZoneIds($request->user());

        $staff = User::query()
            ->where('role', 'delivery_staff')
            ->whereHas('assignedDeliveries', fn (Builder $query) => $query
                ->whereIn('zone_id', $zoneIds)
                ->where(fn (Builder $dateQuery) => $this->applyTodayFilter($dateQuery))
            )
            ->with(['assignedDeliveries' => fn ($query) => $query
                ->whereIn('zone_id', $zoneIds)
                ->where(fn (Builder $dateQuery) => $this->applyTodayFilter($dateQuery))
                ->with(['zone', 'order.items', 'payments']),
            ])
            ->get()
            ->map(function (User $user): array {
                $deliveries = $user->assignedDeliveries;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'zone_name' => $deliveries->first()?->zone?->name,
                    'line_name' => null,
                    'is_active' => true,
                    'assigned_count' => $deliveries->count(),
                    'delivered_count' => $deliveries->whereIn('delivery_status', ['delivered', 'partial_delivered'])->count(),
                    'pending_count' => $deliveries->whereIn('delivery_status', ['pending', 'assigned', 'in_progress'])->count(),
                    'collection_amount' => (float) $deliveries->sum(fn (Delivery $delivery) => $delivery->payments->sum('amount')),
                    'jar_delivered' => (int) $deliveries->whereIn('delivery_status', ['delivered', 'partial_delivered'])->sum(fn (Delivery $delivery) => $this->jarQuantity($delivery)),
                    'empty_jar_returned' => 0,
                ];
            })
            ->values()
            ->all();

        return $this->successResponse('Staff progress loaded successfully.', $staff);
    }

    public function todayDeliveries(Request $request): JsonResponse
    {
        if (! $this->isDeliveryManager($request->user())) {
            return $this->forbiddenResponse();
        }

        $deliveries = $this->todayDeliveriesQuery($request->user())
            ->with($this->deliveryRelations())
            ->orderByRaw("FIELD(delivery_status, 'in_progress', 'assigned', 'pending', 'partial_delivered', 'not_delivered', 'customer_unavailable', 'delivered', 'cancelled', 'failed')")
            ->get()
            ->map(fn (Delivery $delivery) => $this->deliveryPayload($delivery))
            ->values()
            ->all();

        return $this->successResponse('Today deliveries loaded successfully.', $deliveries);
    }

    public function assign(Request $request): JsonResponse
    {
        return $this->assignDelivery(
            $request,
            'Delivery assigned successfully.',
            'New Delivery Assigned',
            'delivery_assigned',
        );
    }

    public function reassign(Request $request): JsonResponse
    {
        return $this->assignDelivery(
            $request,
            'Delivery reassigned successfully.',
            'Delivery Reassigned',
            'delivery_reassigned',
        );
    }

    private function assignDelivery(Request $request, string $message, string $notificationTitle, string $notificationType): JsonResponse
    {
        if (! $this->isDeliveryManager($request->user())) {
            return $this->forbiddenResponse();
        }

        $validator = Validator::make($request->all(), [
            'delivery_id' => ['required', 'integer', 'exists:deliveries,id'],
            'staff_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $staff = User::query()->where('role', 'delivery_staff')->find($data['staff_id']);

        if (! $staff) {
            return $this->validationErrorResponse(['staff_id' => ['The selected staff must be a delivery staff user.']]);
        }

        $delivery = Delivery::with($this->deliveryRelations())->find($data['delivery_id']);

        if (! $delivery || ! in_array((int) $delivery->zone_id, $this->managedZoneIds($request->user()), true)) {
            return $this->forbiddenResponse();
        }

        try {
            DB::transaction(function () use ($delivery, $staff, $request): void {
                $delivery->update([
                    'delivery_staff_id' => $staff->id,
                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                    'delivery_status' => 'assigned',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Delivery assignment failed.', ['delivery_id' => $delivery->id, 'error' => $e->getMessage()]);

            return $this->errorResponse('Server error. Please try again later.', 500);
        }

        $delivery->refresh()->load($this->deliveryRelations());
        $this->notifyAssignedStaff($delivery, $staff, $notificationTitle, $notificationType);

        return $this->successResponse($message, [
            'delivery' => $this->deliveryPayload($delivery),
        ]);
    }

    private function notifyAssignedStaff(Delivery $delivery, User $staff, string $title, string $type): void
    {
        try {
            $orderNo = (string) ($delivery->order?->order_no ?? ('#'.$delivery->order_id));
            $body = $type === 'delivery_reassigned'
                ? sprintf('Order %s has been reassigned to you.', $orderNo)
                : sprintf('Order %s has been assigned to you.', $orderNo);

            SendFirebaseNotificationJob::dispatch(
                [$staff->id],
                $title,
                $body,
                [
                    'type' => $type,
                    'delivery_id' => (string) $delivery->id,
                    'order_no' => $orderNo,
                    'screen' => 'today_deliveries',
                ],
            )->afterCommit();
        } catch (\Throwable $exception) {
            Log::warning('Failed to queue delivery assignment push notification.', [
                'delivery_id' => $delivery->id,
                'staff_id' => $staff->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function todayDeliveriesQuery(User $manager): Builder
    {
        return Delivery::query()
            ->whereIn('zone_id', $this->managedZoneIds($manager))
            ->where(fn (Builder $query) => $this->applyTodayFilter($query));
    }

    private function applyTodayFilter(Builder $query): Builder
    {
        $today = today();

        return $query
            ->whereDate('assigned_at', $today)
            ->orWhereHas('order', fn (Builder $orderQuery) => $orderQuery->whereDate('order_date', $today));
    }

    private function managerPaymentsQuery(User $manager): Builder
    {
        return Payment::query()
            ->where('collection_source', 'delivery_staff')
            ->where('collection_status', 'accepted')
            ->whereDate('payment_date', today())
            ->whereHas('delivery', fn (Builder $query) => $query->whereIn('zone_id', $this->managedZoneIds($manager)));
    }

    private function managedZoneIds(User $manager): array
    {
        return $manager->managedZones()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function deliveryRelations(): array
    {
        return [
            'order.customer.zone',
            'order.dealer.zone',
            'order.items.product',
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
            default => in_array($status, ['pending', 'delivered', 'partial_delivered', 'not_delivered', 'customer_unavailable', 'cancelled'], true)
                ? $status
                : 'pending',
        };
    }

    private function isDeliveryManager(mixed $user): bool
    {
        return $user instanceof User && $user->role === 'delivery_manager';
    }
}
