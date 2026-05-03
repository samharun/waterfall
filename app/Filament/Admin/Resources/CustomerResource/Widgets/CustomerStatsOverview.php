<?php

namespace App\Filament\Admin\Resources\CustomerResource\Widgets;

use App\Filament\Admin\Resources\Widgets\CustomerMenuStatsWidget;
use App\Models\Customer;

class CustomerStatsOverview extends CustomerMenuStatsWidget
{
    public function getCards(): array
    {
        $pending = Customer::where('approval_status', 'pending')->count();

        return [
            [
                'label' => 'Total Customers',
                'value' => number_format(Customer::count()),
                'tone' => 'blue',
            ],
            [
                'label' => 'Approved',
                'value' => number_format(Customer::where('approval_status', 'approved')->count()),
                'tone' => 'green',
            ],
            [
                'label' => 'Pending Approval',
                'value' => number_format($pending),
                'tone' => 'amber',
                'hint' => $pending > 0 ? 'Needs review' : 'All clear',
            ],
            [
                'label' => 'Current Due',
                'value' => 'BDT ' . number_format((float) Customer::sum('current_due'), 2),
                'tone' => 'white',
            ],
        ];
    }
}
