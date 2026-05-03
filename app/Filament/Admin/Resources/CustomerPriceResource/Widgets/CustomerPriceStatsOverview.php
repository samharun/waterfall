<?php

namespace App\Filament\Admin\Resources\CustomerPriceResource\Widgets;

use App\Filament\Admin\Resources\Widgets\CustomerMenuStatsWidget;
use App\Models\CustomerPrice;

class CustomerPriceStatsOverview extends CustomerMenuStatsWidget
{
    public function getCards(): array
    {
        return [
            [
                'label' => 'Pricing Rules',
                'value' => number_format(CustomerPrice::count()),
                'tone' => 'blue',
            ],
            [
                'label' => 'Active Rules',
                'value' => number_format(CustomerPrice::active()->count()),
                'tone' => 'green',
            ],
            [
                'label' => 'Effective Today',
                'value' => number_format(CustomerPrice::currentlyEffective()->count()),
                'tone' => 'amber',
            ],
            [
                'label' => 'Average Price',
                'value' => 'BDT ' . number_format((float) CustomerPrice::avg('custom_price'), 2),
                'tone' => 'white',
            ],
        ];
    }
}
