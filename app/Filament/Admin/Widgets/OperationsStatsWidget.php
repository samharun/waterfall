<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Delivery;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    protected function getStats(): array
    {
        $pendingDeliveries = Delivery::whereIn('delivery_status', ['pending', 'assigned', 'in_progress'])
            ->count();

        $totalCustomerDue = Customer::sum('current_due');
        $totalDealerDue   = Dealer::sum('current_due');

        $lowStockCount = Product::where('status', 'active')
            ->whereColumn('current_stock', '<=', 'stock_alert_qty')
            ->count();

        $totalJarDeposit = Customer::sum('jar_deposit_qty');

        return [
            Stat::make('Pending Deliveries', $pendingDeliveries)
                ->description('Pending / Assigned / In Progress')
                ->icon('heroicon-o-truck')
                ->color($pendingDeliveries > 0 ? 'warning' : 'success'),

            Stat::make('Customer Due', '৳ ' . number_format((float) $totalCustomerDue, 2))
                ->description('Total outstanding customer dues')
                ->icon('heroicon-o-exclamation-circle')
                ->color((float) $totalCustomerDue > 0 ? 'danger' : 'success'),

            Stat::make('Dealer Due', '৳ ' . number_format((float) $totalDealerDue, 2))
                ->description('Total outstanding dealer dues')
                ->icon('heroicon-o-exclamation-triangle')
                ->color((float) $totalDealerDue > 0 ? 'danger' : 'success'),

            Stat::make('Low Stock Products', $lowStockCount)
                ->description('Active products at or below alert qty')
                ->icon('heroicon-o-archive-box-x-mark')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Jars With Customers', $totalJarDeposit)
                ->description('Total jar deposit qty (customers + dealers)')
                ->icon('heroicon-o-archive-box')
                ->color('info'),
        ];
    }
}
