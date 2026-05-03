<?php

namespace App\Filament\Admin\Resources\CustomerSubscriptionResource\Widgets;

use App\Filament\Admin\Resources\Widgets\CustomerMenuStatsWidget;
use App\Models\CustomerSubscription;

class SubscriptionStatsOverview extends CustomerMenuStatsWidget
{
    public function getCards(): array
    {
        return [
            [
                'label' => 'Subscriptions',
                'value' => number_format(CustomerSubscription::count()),
                'tone' => 'blue',
            ],
            [
                'label' => 'Active',
                'value' => number_format(CustomerSubscription::active()->count()),
                'tone' => 'green',
            ],
            [
                'label' => 'Due Today',
                'value' => number_format(CustomerSubscription::dueForDelivery()->count()),
                'tone' => 'amber',
            ],
            [
                'label' => 'Paused',
                'value' => number_format(CustomerSubscription::paused()->count()),
                'tone' => 'white',
            ],
        ];
    }
}
