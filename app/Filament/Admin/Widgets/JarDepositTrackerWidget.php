<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Models\JarDeposit;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

/**
 * Low Priority Widget 1: Jar Deposit Tracker
 *
 * Shows total jars currently out with customers/dealers,
 * today's jar movements (issued vs returned), and top holders.
 */
class JarDepositTrackerWidget extends Widget
{
    protected static ?int $sort = 9;

    protected string $view = 'filament.admin.widgets.jar-deposit-tracker';

    protected static ?string $pollingInterval = '120s';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.dashboard.view') ?? false;
    }

    public function getData(): array
    {
        $today = Carbon::today();

        // Total jars currently out with customers (stored on customers table)
        $totalCustomerJars = (int) Customer::sum('jar_deposit_qty');

        // Total jars with dealers — calculated from jar_deposit transactions
        // (dealers table may not have jar_deposit_qty column)
        $dealerIssued   = (int) JarDeposit::where('party_type', 'dealer')
            ->whereIn('transaction_type', ['jar_issued', 'deposit_received'])
            ->sum('quantity');
        $dealerReturned = (int) JarDeposit::where('party_type', 'dealer')
            ->where('transaction_type', 'jar_returned')
            ->sum('quantity');
        $totalDealerJars = max(0, $dealerIssued - $dealerReturned);

        $totalJarsOut = $totalCustomerJars + $totalDealerJars;

        // Today's movements
        $issuedToday   = (int) JarDeposit::whereDate('transaction_date', $today)
            ->where('transaction_type', 'jar_issued')
            ->sum('quantity');

        $returnedToday = (int) JarDeposit::whereDate('transaction_date', $today)
            ->where('transaction_type', 'jar_returned')
            ->sum('quantity');

        $netMovement = $issuedToday - $returnedToday;

        // Last 7 days movements
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            return [
                'label'    => $date->format('D'),
                'issued'   => (int) JarDeposit::whereDate('transaction_date', $date)
                    ->where('transaction_type', 'jar_issued')->sum('quantity'),
                'returned' => (int) JarDeposit::whereDate('transaction_date', $date)
                    ->where('transaction_type', 'jar_returned')->sum('quantity'),
            ];
        });

        // Top 5 customers by jar count
        $topCustomers = Customer::where('jar_deposit_qty', '>', 0)
            ->orderByDesc('jar_deposit_qty')
            ->limit(5)
            ->get(['id', 'name', 'customer_id', 'jar_deposit_qty']);

        return [
            'total_jars_out'     => $totalJarsOut,
            'customer_jars'      => $totalCustomerJars,
            'dealer_jars'        => $totalDealerJars,
            'issued_today'       => $issuedToday,
            'returned_today'     => $returnedToday,
            'net_movement'       => $netMovement,
            'last_7_days'        => $last7Days,
            'top_customers'      => $topCustomers,
        ];
    }
}
