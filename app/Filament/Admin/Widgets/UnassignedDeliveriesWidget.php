<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Delivery;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Medium Priority Widget 3: Unassigned Deliveries Alert
 *
 * Shows deliveries that have no staff assigned yet.
 * Allows quick assignment directly from the dashboard.
 */
class UnassignedDeliveriesWidget extends Widget
{
    protected static ?int $sort = 8;

    protected string $view = 'filament.admin.widgets.unassigned-deliveries';

    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return auth()->user()?->can('deliveries.assign') ?? false;
    }

    public function getUnassignedDeliveries(): Collection
    {
        return Delivery::with(['order.customer', 'order.dealer', 'zone'])
            ->whereNull('delivery_staff_id')
            ->whereNotIn('delivery_status', ['cancelled', 'delivered'])
            ->orderBy('created_at', 'asc') // oldest first — most urgent
            ->limit(15)
            ->get()
            ->map(function (Delivery $delivery) {
                $order = $delivery->order;
                $party = $order?->order_type === 'dealer'
                    ? $order->dealer
                    : $order?->customer;

                return [
                    'id'           => $delivery->id,
                    'delivery_no'  => $delivery->delivery_no,
                    'party_name'   => $party?->name ?? '—',
                    'party_mobile' => $party?->mobile ?? '—',
                    'zone'         => $delivery->zone?->name ?? '—',
                    'status'       => $delivery->delivery_status,
                    'created_at'   => $delivery->created_at?->diffForHumans() ?? '—',
                    'order_type'   => $order?->order_type ?? '—',
                ];
            });
    }

    public function getTotalUnassigned(): int
    {
        return Delivery::whereNull('delivery_staff_id')
            ->whereNotIn('delivery_status', ['cancelled', 'delivered'])
            ->count();
    }

    public function getAvailableStaff(): Collection
    {
        return User::where('role', 'delivery_staff')
            ->orderBy('name')
            ->get(['id', 'name', 'mobile']);
    }
}
