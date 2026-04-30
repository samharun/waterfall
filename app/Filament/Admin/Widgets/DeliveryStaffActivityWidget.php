<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Delivery;
use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Widget 2 (sort 4): Delivery Staff Activity — Today
 *
 * Shows each delivery staff member's progress for today:
 * assigned, delivered, pending, failed, and cash collected.
 * Mirrors what the Flutter delivery manager app shows.
 */
class DeliveryStaffActivityWidget extends Widget
{
    protected static ?int $sort = 4;

    protected string $view = 'filament.admin.widgets.delivery-staff-activity';

    protected static ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    /**
     * Returns a collection of staff rows for today's activity.
     */
    public function getStaffRows(): Collection
    {
        $today = Carbon::today();

        // Get all delivery staff who have at least one delivery assigned today
        $staffIds = Delivery::whereDate('assigned_at', $today)
            ->whereNotNull('delivery_staff_id')
            ->pluck('delivery_staff_id')
            ->unique();

        if ($staffIds->isEmpty()) {
            return collect();
        }

        $staff = User::whereIn('id', $staffIds)
            ->where('role', 'delivery_staff')
            ->orderBy('name')
            ->get();

        return $staff->map(function (User $user) use ($today) {
            $deliveries = Delivery::where('delivery_staff_id', $user->id)
                ->whereDate('assigned_at', $today)
                ->get();

            $assigned   = $deliveries->count();
            $delivered  = $deliveries->where('delivery_status', 'delivered')->count();
            $inProgress = $deliveries->whereIn('delivery_status', ['assigned', 'in_progress'])->count();
            $failed     = $deliveries->whereIn('delivery_status', [
                'failed', 'not_delivered', 'customer_unavailable', 'partial_delivered',
            ])->count();

            // Cash collected by this staff today
            $collected = Payment::where('collected_from_staff_id', $user->id)
                ->orWhere(function ($q) use ($user, $today) {
                    $q->where('collection_source', 'delivery_staff')
                      ->whereDate('payment_date', $today)
                      ->whereHas('delivery', fn ($d) => $d->where('delivery_staff_id', $user->id));
                })
                ->whereDate('payment_date', $today)
                ->where('collection_status', 'accepted')
                ->sum('amount');

            $completionPct = $assigned > 0
                ? round(($delivered / $assigned) * 100)
                : 0;

            return [
                'id'             => $user->id,
                'name'           => $user->name,
                'mobile'         => $user->mobile ?? '—',
                'assigned'       => $assigned,
                'delivered'      => $delivered,
                'in_progress'    => $inProgress,
                'failed'         => $failed,
                'collected'      => (float) $collected,
                'completion_pct' => $completionPct,
                'status_color'   => $completionPct >= 80 ? 'success'
                    : ($completionPct >= 40 ? 'warning' : 'danger'),
            ];
        })->sortByDesc('completion_pct')->values();
    }
}
