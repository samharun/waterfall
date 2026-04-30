<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Delivery;
use App\Models\Payment;
use App\Models\Zone;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Widget 3 (sort 5): Zone-wise Delivery Summary — Today
 *
 * Shows per-zone delivery stats for today.
 * Especially useful now that managers can be assigned to multiple zones.
 */
class ZoneDeliverySummaryWidget extends Widget
{
    protected static ?int $sort = 5;

    protected string $view = 'filament.admin.widgets.zone-delivery-summary';

    protected static ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    /**
     * Returns per-zone stats for today's deliveries.
     * Only includes zones that have at least one delivery today.
     */
    public function getZoneRows(): Collection
    {
        $today = Carbon::today();

        // Get zone IDs that have deliveries today
        $zoneIds = Delivery::whereDate('assigned_at', $today)
            ->whereNotNull('zone_id')
            ->pluck('zone_id')
            ->unique();

        if ($zoneIds->isEmpty()) {
            return collect();
        }

        $zones = Zone::whereIn('id', $zoneIds)
            ->orderBy('name')
            ->get();

        return $zones->map(function (Zone $zone) use ($today) {
            $deliveries = Delivery::where('zone_id', $zone->id)
                ->whereDate('assigned_at', $today)
                ->get();

            $total      = $deliveries->count();
            $delivered  = $deliveries->where('delivery_status', 'delivered')->count();
            $pending    = $deliveries->whereIn('delivery_status', [
                'pending', 'assigned', 'in_progress',
            ])->count();
            $failed     = $deliveries->whereIn('delivery_status', [
                'failed', 'not_delivered', 'customer_unavailable', 'partial_delivered',
            ])->count();
            $unassigned = $deliveries->whereNull('delivery_staff_id')
                ->whereNotIn('delivery_status', ['cancelled'])
                ->count();

            // Collection for this zone today
            $collection = Payment::whereDate('payment_date', $today)
                ->where('collection_source', 'delivery_staff')
                ->where('collection_status', 'accepted')
                ->whereHas('delivery', fn ($q) => $q->where('zone_id', $zone->id))
                ->sum('amount');

            // Active staff count in this zone today
            $activeStaff = Delivery::where('zone_id', $zone->id)
                ->whereDate('assigned_at', $today)
                ->whereNotNull('delivery_staff_id')
                ->distinct('delivery_staff_id')
                ->count('delivery_staff_id');

            $completionPct = $total > 0
                ? round(($delivered / $total) * 100)
                : 0;

            return [
                'zone_id'        => $zone->id,
                'zone_name'      => $zone->name,
                'zone_code'      => $zone->code,
                'manager'        => $zone->deliveryManager?->name ?? '—',
                'total'          => $total,
                'delivered'      => $delivered,
                'pending'        => $pending,
                'failed'         => $failed,
                'unassigned'     => $unassigned,
                'active_staff'   => $activeStaff,
                'collection'     => (float) $collection,
                'completion_pct' => $completionPct,
                'status_color'   => $completionPct >= 80 ? 'success'
                    : ($completionPct >= 40 ? 'warning' : 'danger'),
            ];
        })->sortByDesc('completion_pct')->values();
    }
}
