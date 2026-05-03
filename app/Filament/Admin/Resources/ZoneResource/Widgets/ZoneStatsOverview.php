<?php

namespace App\Filament\Admin\Resources\ZoneResource\Widgets;

use App\Filament\Admin\Resources\Widgets\CustomerMenuStatsWidget;
use App\Models\Customer;
use App\Models\Zone;

class ZoneStatsOverview extends CustomerMenuStatsWidget
{
    public function getCards(): array
    {
        return [
            [
                'label' => 'Total Zones',
                'value' => number_format(Zone::count()),
                'tone' => 'blue',
            ],
            [
                'label' => 'Active Zones',
                'value' => number_format(Zone::where('status', 'active')->count()),
                'tone' => 'green',
            ],
            [
                'label' => 'Inactive Zones',
                'value' => number_format(Zone::where('status', 'inactive')->count()),
                'tone' => 'amber',
            ],
            [
                'label' => 'Assigned Customers',
                'value' => number_format(Customer::whereNotNull('zone_id')->count()),
                'tone' => 'white',
            ],
        ];
    }
}
