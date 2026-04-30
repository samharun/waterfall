<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Payment;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

/**
 * Low Priority Widget 2: Recent Activity Feed
 *
 * Shows a unified feed of recent events:
 * - Delivery status changes
 * - New customer registrations
 * - Payments received
 */
class RecentActivityWidget extends Widget
{
    protected static ?int $sort = 10;

    protected string $view = 'filament.admin.widgets.recent-activity';

    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    public function getActivities(): Collection
    {
        $activities = collect();

        // Recent delivery status changes (last 20)
        $deliveries = Delivery::with(['order.customer', 'order.dealer', 'deliveryStaff'])
            ->whereNotNull('updated_at')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        foreach ($deliveries as $delivery) {
            $order = $delivery->order;
            $party = $order?->order_type === 'dealer'
                ? $order->dealer
                : $order?->customer;

            $statusColors = [
                'delivered'            => ['bg' => '#f0fdf4', 'dot' => '#16a34a', 'text' => '#15803d'],
                'assigned'             => ['bg' => '#eff6ff', 'dot' => '#3b82f6', 'text' => '#1d4ed8'],
                'in_progress'          => ['bg' => '#eff6ff', 'dot' => '#2563eb', 'text' => '#1e40af'],
                'failed'               => ['bg' => '#fef2f2', 'dot' => '#dc2626', 'text' => '#b91c1c'],
                'not_delivered'        => ['bg' => '#fef2f2', 'dot' => '#dc2626', 'text' => '#b91c1c'],
                'customer_unavailable' => ['bg' => '#fff7ed', 'dot' => '#f97316', 'text' => '#9a3412'],
                'partial_delivered'    => ['bg' => '#fff7ed', 'dot' => '#f59e0b', 'text' => '#92400e'],
                'cancelled'            => ['bg' => '#f9fafb', 'dot' => '#9ca3af', 'text' => '#6b7280'],
                'pending'              => ['bg' => '#f9fafb', 'dot' => '#9ca3af', 'text' => '#6b7280'],
            ];

            $colors = $statusColors[$delivery->delivery_status] ?? $statusColors['pending'];

            $activities->push([
                'type'       => 'delivery',
                'icon'       => 'truck',
                'title'      => $party?->name ?? 'Unknown',
                'subtitle'   => ucwords(str_replace('_', ' ', $delivery->delivery_status))
                    . ($delivery->deliveryStaff ? ' · ' . $delivery->deliveryStaff->name : ''),
                'meta'       => $delivery->delivery_no,
                'time'       => $delivery->updated_at,
                'colors'     => $colors,
            ]);
        }

        // Recent customer registrations (last 5)
        $customers = Customer::orderByDesc('created_at')->limit(5)->get();
        foreach ($customers as $customer) {
            $activities->push([
                'type'     => 'customer',
                'icon'     => 'user',
                'title'    => $customer->name,
                'subtitle' => 'New customer registered · ' . ucfirst($customer->approval_status),
                'meta'     => $customer->customer_id,
                'time'     => $customer->created_at,
                'colors'   => ['bg' => '#f5f3ff', 'dot' => '#7c3aed', 'text' => '#5b21b6'],
            ]);
        }

        // Recent payments (last 5)
        $payments = Payment::with(['customer', 'dealer'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($payments as $payment) {
            $party = $payment->customer ?? $payment->dealer;
            $activities->push([
                'type'     => 'payment',
                'icon'     => 'cash',
                'title'    => $party?->name ?? 'Unknown',
                'subtitle' => '৳ ' . number_format((float) $payment->amount, 2)
                    . ' · ' . ucfirst($payment->payment_method ?? 'cash'),
                'meta'     => $payment->payment_no,
                'time'     => $payment->created_at,
                'colors'   => ['bg' => '#ecfdf5', 'dot' => '#059669', 'text' => '#065f46'],
            ]);
        }

        // Sort all by time descending, take top 15
        return $activities
            ->sortByDesc('time')
            ->values()
            ->take(15);
    }
}
