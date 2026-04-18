<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    protected function getStats(): array
    {
        $activeCustomers  = Customer::where('approval_status', 'approved')->count();
        $pendingCustomers = Customer::where('approval_status', 'pending')->count();
        $approvedDealers  = Dealer::where('approval_status', 'approved')->count();
        $todayOrders      = Order::whereDate('order_date', today())->count();
        $todayDelivered   = Order::whereDate('order_date', today())
            ->where('order_status', 'delivered')->count();

        $monthSales = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        return [
            Stat::make('Active Customers', $activeCustomers)
                ->description('Approved customers')
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make('Pending Approvals', $pendingCustomers)
                ->description('Customers awaiting approval')
                ->icon('heroicon-o-clock')
                ->color($pendingCustomers > 0 ? 'warning' : 'gray'),

            Stat::make('Approved Dealers', $approvedDealers)
                ->description('Active dealers')
                ->icon('heroicon-o-building-storefront')
                ->color('info'),

            Stat::make("Today's Orders", $todayOrders)
                ->description('Orders placed today')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary'),

            Stat::make("Today's Delivered", $todayDelivered)
                ->description('Delivered today')
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('This Month Sales', '৳ ' . number_format((float) $monthSales, 2))
                ->description(Carbon::now()->format('F Y'))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
