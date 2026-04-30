<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Delivery;
use App\Models\Payment;
use App\Models\Zone;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Medium Priority Widget 2: Today's Collection Summary
 *
 * Shows cash collected today by delivery staff,
 * broken down by zone and payment method.
 */
class TodayCollectionSummaryWidget extends Widget
{
    protected static ?int $sort = 7;

    protected string $view = 'filament.admin.widgets.today-collection-summary';

    protected static ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    public function getSummary(): array
    {
        $today = Carbon::today();

        // All payments collected by delivery staff today
        $payments = Payment::with(['delivery.zone'])
            ->whereDate('payment_date', $today)
            ->where('collection_source', 'delivery_staff')
            ->where('collection_status', 'accepted')
            ->get();

        $totalCollected  = (float) $payments->sum('amount');
        $totalCount      = $payments->count();

        // By payment method
        $byMethod = $payments->groupBy('payment_method')
            ->map(fn ($group) => [
                'count'  => $group->count(),
                'amount' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('amount')
            ->values();

        // By zone
        $byZone = $payments->groupBy(fn ($p) => $p->delivery?->zone?->name ?? 'Unknown')
            ->map(fn ($group, $zoneName) => [
                'zone'   => $zoneName,
                'count'  => $group->count(),
                'amount' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('amount')
            ->values();

        // Pending review (collected but not yet accepted)
        $pendingReview = Payment::whereDate('payment_date', $today)
            ->where('collection_source', 'delivery_staff')
            ->where('collection_status', 'pending_review')
            ->sum('amount');

        return [
            'total_collected'  => $totalCollected,
            'total_count'      => $totalCount,
            'pending_review'   => (float) $pendingReview,
            'by_method'        => $byMethod,
            'by_zone'          => $byZone,
            'date_label'       => Carbon::today()->format('d M Y'),
        ];
    }

    private static array $methodLabels = [
        'cash'  => 'Cash',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'bank'  => 'Bank',
        'card'  => 'Card',
        'other' => 'Other',
    ];

    public static function methodLabel(string $method): string
    {
        return self::$methodLabels[$method] ?? ucfirst($method);
    }
}
