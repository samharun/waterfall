<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DailyOrdersChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    protected ?string $heading = 'Daily Orders (Last 7 Days)';

    protected string $color = 'primary';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $days   = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $labels = $days->map(fn ($d) => $d->format('D d/m'))->toArray();

        $counts = $days->map(fn ($d) => Order::whereDate('order_date', $d)->count())->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Orders',
                    'data'            => $counts,
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
