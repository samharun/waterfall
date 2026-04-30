<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 7;

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        // Only show revenue chart to roles with billing visibility
        $user = auth()->user();
        return $user?->can('reports.sales.view') || $user?->can('reports.dashboard.view') && $user?->isBackOffice();
    }

    protected ?string $heading = 'Monthly Revenue (Last 6 Months)';

    protected string $color = 'success';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));
        $labels = $months->map(fn ($m) => $m->format('M Y'))->toArray();

        $totals = $months->map(fn ($m) => (float) Payment::whereYear('payment_date', $m->year)
            ->whereMonth('payment_date', $m->month)
            ->sum('amount')
        )->toArray();

        return [
            'datasets' => [
                [
                    'label'       => 'Revenue (৳)',
                    'data'        => $totals,
                    'borderColor' => '#10b981',
                    'fill'        => false,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
