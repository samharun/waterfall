<?php

namespace App\Filament\Admin\Pages;

use App\Services\DeliveryStaffLocationMapService;
use Filament\Pages\Page;

class DeliveryStaffMap extends Page
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'deliveries.view';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';
    protected static string|\UnitEnum|null $navigationGroup = 'Order & Delivery';
    protected static ?string $navigationLabel = 'Delivery Staff Map';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.delivery-staff-map';

    public function getInitialMapData(): array
    {
        $mapService = app(DeliveryStaffLocationMapService::class);
        $markers = $mapService->markers();

        return [
            'markers' => $markers,
            'stats' => $mapService->stats($markers),
            'refreshed_at' => now()->toIso8601String(),
        ];
    }
}
