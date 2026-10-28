<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Widgets\Concerns\RefreshesWithDashboard;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OverviewStatsWidget extends StatsOverviewWidget
{
    use RefreshesWithDashboard;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = 'Business snapshot';

    protected ?string $description = 'Customers, orders, and accepted customer/dealer collections';

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    protected function getStats(): array
    {
        $activeCustomers = Customer::where('approval_status', 'approved')->count();
        $pendingCustomers = Customer::where('approval_status', 'pending')->count();
        $approvedDealers = Dealer::where('approval_status', 'approved')->count();
        $todayOrders = Order::whereDate('order_date', today())->count();
        $todayDelivered = Order::whereDate('order_date', today())
            ->where('order_status', 'delivered')->count();

        $collections = app(\App\Services\Accounts\CollectionSummaryService::class)->totals();

        return [
            Stat::make('Collected Today', '৳ '.number_format($collections['today'], 2))
                ->description('Accepted payments · all collectors')
                ->icon('heroicon-o-banknotes')->color('success'),
            Stat::make('Collected This Month', '৳ '.number_format($collections['month'], 2))
                ->description(Carbon::now()->format('F Y').' · accepted payments')
                ->icon('heroicon-o-calendar-days')->color('success'),
            Stat::make('Total Collected', '৳ '.number_format($collections['all_time'], 2))
                ->description('All time · accepted customer/dealer payments')
                ->icon('heroicon-o-banknotes')->color('success'),
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

        ];
    }
}
