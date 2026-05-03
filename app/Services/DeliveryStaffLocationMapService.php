<?php

namespace App\Services;

use App\Models\DeliveryStaffLocation;
use App\Models\User;
use Illuminate\Support\Carbon;

class DeliveryStaffLocationMapService
{
    public function markers(): array
    {
        return DeliveryStaffLocation::query()
            ->with([
                'staff.currentActiveDelivery.order.customer.zone',
                'staff.currentActiveDelivery.order.dealer.zone',
                'staff.currentActiveDelivery.zone',
            ])
            ->whereHas('staff', fn ($query) => $query->where('role', 'delivery_staff'))
            ->orderByDesc('tracked_at')
            ->get()
            ->map(fn (DeliveryStaffLocation $location) => $this->markerPayload($location))
            ->values()
            ->all();
    }

    public function stats(array $markers): array
    {
        $totalStaff = User::deliveryStaff()->count();
        $tracked = count($markers);

        return [
            'total_staff' => $totalStaff,
            'tracked' => $tracked,
            'online' => collect($markers)->where('status', 'online')->count(),
            'stale' => collect($markers)->where('status', 'stale')->count(),
            'offline' => collect($markers)->where('status', 'offline')->count(),
            'missing' => max(0, $totalStaff - $tracked),
        ];
    }

    private function markerPayload(DeliveryStaffLocation $location): array
    {
        $staff = $location->staff;
        $delivery = $staff?->currentActiveDelivery;
        $order = $delivery?->order;
        $party = $order?->customer ?? $order?->dealer;
        $trackedAt = $location->tracked_at ?? $location->updated_at;

        return [
            'staff_id' => $staff?->id,
            'staff_name' => $staff?->name ?? 'Unknown Staff',
            'mobile' => $staff?->mobile,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'accuracy' => $location->accuracy,
            'speed' => $location->speed,
            'heading' => $location->heading,
            'battery_level' => $location->battery_level,
            'tracked_at' => $trackedAt?->toIso8601String(),
            'last_seen' => $trackedAt?->diffForHumans(),
            'status' => $this->freshnessStatus($trackedAt),
            'delivery' => $delivery ? [
                'id' => $delivery->id,
                'delivery_no' => $delivery->delivery_no,
                'status' => $delivery->delivery_status,
                'zone' => $delivery->zone?->name ?? $party?->zone?->name,
                'order_no' => $order?->order_no,
                'party_name' => $party?->name,
                'party_address' => $party?->address,
            ] : null,
        ];
    }

    private function freshnessStatus(?Carbon $trackedAt): string
    {
        if (! $trackedAt) {
            return 'offline';
        }

        if ($trackedAt->greaterThanOrEqualTo(now()->subMinutes(15))) {
            return 'online';
        }

        if ($trackedAt->greaterThanOrEqualTo(now()->subHour())) {
            return 'stale';
        }

        return 'offline';
    }
}
