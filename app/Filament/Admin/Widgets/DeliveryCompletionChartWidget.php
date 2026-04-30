<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Delivery;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

/**
 * Low Priority Widget 3: Monthly Delivery Completion Chart
 *
 * Shows delivered vs total assigned per day for the last 14 days.
 * Rendered as an inline SVG bar chart (no JS library needed).
 */
class DeliveryCompletionChartWidget extends Widget
{
    protected static ?int $sort = 11;

    protected string $view = 'filament.admin.widgets.delivery-completion-chart';

    protected static ?string $pollingInterval = '300s'; // 5 min — chart data changes slowly

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    public function getChartData(): array
    {
        $days = collect(range(13, 0))->map(function ($daysAgo) {
            $date  = Carbon::today()->subDays($daysAgo);
            $total = Delivery::whereDate('assigned_at', $date)->count();
            $done  = Delivery::whereDate('assigned_at', $date)
                ->where('delivery_status', 'delivered')
                ->count();
            $pct   = $total > 0 ? round(($done / $total) * 100) : 0;

            return [
                'label'   => $date->format('d/m'),
                'day'     => $date->format('D'),
                'total'   => $total,
                'done'    => $done,
                'failed'  => Delivery::whereDate('assigned_at', $date)
                    ->whereIn('delivery_status', ['failed', 'not_delivered', 'customer_unavailable'])
                    ->count(),
                'pct'     => $pct,
                'is_today'=> $daysAgo === 0,
            ];
        });

        $avgPct = $days->where('total', '>', 0)->avg('pct') ?? 0;

        return [
            'days'    => $days,
            'avg_pct' => round($avgPct, 1),
            'total_delivered_period' => $days->sum('done'),
            'total_assigned_period'  => $days->sum('total'),
        ];
    }
}
