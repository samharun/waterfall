<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Delivery;
use App\Models\Payment;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

/**
 * Widget 1 (sort 3): Today's Delivery Status Breakdown
 *
 * Shows a live status breakdown of all deliveries assigned today,
 * with a completion progress bar and today's collection total.
 */
class TodayDeliveryStatusWidget extends Widget
{
    protected static ?int $sort = 3;

    protected string $view = 'filament.admin.widgets.today-delivery-status';

    // Auto-refresh every 60 seconds
    protected static ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    /**
     * Gather all stats needed by the view.
     */
    public function getStats(): array
    {
        $today = Carbon::today();

        // All deliveries assigned today (regardless of current status)
        $base = Delivery::whereDate('assigned_at', $today);

        $total       = (clone $base)->count();
        $pending     = (clone $base)->where('delivery_status', 'pending')->count();
        $assigned    = (clone $base)->where('delivery_status', 'assigned')->count();
        $inProgress  = (clone $base)->where('delivery_status', 'in_progress')->count();
        $delivered   = (clone $base)->where('delivery_status', 'delivered')->count();
        $failed      = (clone $base)->whereIn('delivery_status', [
            'failed', 'not_delivered', 'customer_unavailable', 'partial_delivered',
        ])->count();
        $cancelled   = (clone $base)->where('delivery_status', 'cancelled')->count();

        // Unassigned: created today but no staff assigned yet
        $unassigned = Delivery::whereDate('created_at', $today)
            ->whereNull('delivery_staff_id')
            ->whereNotIn('delivery_status', ['cancelled'])
            ->count();

        // Today's collection (payments collected by delivery staff today)
        $todayCollection = Payment::whereDate('payment_date', $today)
            ->where('collection_source', 'delivery_staff')
            ->where('collection_status', 'accepted')
            ->sum('amount');

        $completionPct = $total > 0
            ? round(($delivered / $total) * 100, 1)
            : 0;

        return [
            'total'           => $total,
            'pending'         => $pending,
            'assigned'        => $assigned,
            'in_progress'     => $inProgress,
            'delivered'       => $delivered,
            'failed'          => $failed,
            'cancelled'       => $cancelled,
            'unassigned'      => $unassigned,
            'completion_pct'  => $completionPct,
            'today_collection'=> (float) $todayCollection,
            'date_label'      => Carbon::today()->format('l, d M Y'),
        ];
    }
}
