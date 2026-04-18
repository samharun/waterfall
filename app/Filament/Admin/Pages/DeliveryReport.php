<?php

namespace App\Filament\Admin\Pages;

use App\Models\Delivery;
use App\Models\User;
use App\Models\Zone;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DeliveryReport extends Page implements HasForms
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'reports.delivery.view';
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Delivery Report';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.admin.pages.delivery-report';

    public ?string $assigned_from     = null;
    public ?string $assigned_until    = null;
    public ?string $delivered_from    = null;
    public ?string $delivered_until   = null;
    public ?string $delivery_status   = null;
    public ?int    $zone_id           = null;
    public ?int    $delivery_staff_id = null;

    public function mount(): void
    {
        $this->assigned_from  = Carbon::now()->startOfMonth()->toDateString();
        $this->assigned_until = Carbon::now()->toDateString();
    }

    public function getDeliveries(): Collection
    {
        return Delivery::with(['order.customer', 'order.dealer', 'zone', 'deliveryStaff'])
            ->when($this->assigned_from,     fn ($q) => $q->whereDate('assigned_at', '>=', $this->assigned_from))
            ->when($this->assigned_until,    fn ($q) => $q->whereDate('assigned_at', '<=', $this->assigned_until))
            ->when($this->delivered_from,    fn ($q) => $q->whereDate('delivered_at', '>=', $this->delivered_from))
            ->when($this->delivered_until,   fn ($q) => $q->whereDate('delivered_at', '<=', $this->delivered_until))
            ->when($this->delivery_status,   fn ($q) => $q->where('delivery_status', $this->delivery_status))
            ->when($this->zone_id,           fn ($q) => $q->where('zone_id', $this->zone_id))
            ->when($this->delivery_staff_id, fn ($q) => $q->where('delivery_staff_id', $this->delivery_staff_id))
            ->orderByDesc('created_at')
            ->get();
    }
}
