<?php

namespace App\Filament\Admin\Resources\CustomerSubscriptionResource\Pages;

use App\Filament\Admin\Resources\CustomerSubscriptionResource;
use App\Filament\Admin\Resources\CustomerSubscriptionResource\Widgets\SubscriptionStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerSubscriptions extends ListRecords
{
    protected static string $resource = CustomerSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SubscriptionStatsOverview::class,
        ];
    }
}
