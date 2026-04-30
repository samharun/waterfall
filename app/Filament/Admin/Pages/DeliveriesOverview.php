<?php

namespace App\Filament\Admin\Pages;

use App\Models\Delivery;
use App\Models\Payment;
use App\Models\User;
use App\Models\Zone;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DeliveriesOverview extends Page
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'deliveries.view';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static string|\UnitEnum|null $navigationGroup = 'Order & Delivery';
    protected static ?string $navigationLabel = 'Deliveries Overview';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.admin.pages.deliveries-overview';

    // ── Filter state ───────────────────────────────────────────────
    public ?string $date_from         = null;
    public ?string $date_until        = null;
    public ?string $delivery_status   = null;
    public ?int    $zone_id           = null;
    public ?int    $delivery_staff_id = null;

    public function mount(): void
    {
        $this->date_from  = Carbon::now()->startOfMonth()->toDateString();
        $this->date_until = Carbon::now()->toDateString();
    }

    public function getDeliveries(): Collection
    {
        return Delivery::with(['order.customer', 'order.dealer', 'zone', 'deliveryStaff'])
            ->when($this->date_from,         fn ($q) => $q->whereDate('assigned_at', '>=', $this->date_from))
            ->when($this->date_until,        fn ($q) => $q->whereDate('assigned_at', '<=', $this->date_until))
            ->when($this->delivery_status,   fn ($q) => $q->where('delivery_status', $this->delivery_status))
            ->when($this->zone_id,           fn ($q) => $q->where('zone_id', $this->zone_id))
            ->when($this->delivery_staff_id, fn ($q) => $q->where('delivery_staff_id', $this->delivery_staff_id))
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->limit(300)
            ->get();
    }

    public function getStats(): array
    {
        $deliveries  = $this->getDeliveries();
        $total       = $deliveries->count();
        $pending     = $deliveries->whereIn('delivery_status', ['pending', 'assigned', 'in_progress'])->count();
        $delivered   = $deliveries->where('delivery_status', 'delivered')->count();
        $failed      = $deliveries->whereIn('delivery_status', ['failed', 'not_delivered', 'customer_unavailable'])->count();
        $cancelled   = $deliveries->where('delivery_status', 'cancelled')->count();
        $unassigned  = $deliveries->whereNull('delivery_staff_id')
            ->whereNotIn('delivery_status', ['cancelled'])->count();
        $nonCancelled = $total - $cancelled;
        $successRate  = $nonCancelled > 0 ? round(($delivered / $nonCancelled) * 100, 1) : 0;

        // Collection for filtered period
        $deliveryIds = $deliveries->pluck('id');
        $collection  = $deliveryIds->isNotEmpty()
            ? (float) Payment::whereIn('delivery_id', $deliveryIds)
                ->where('collection_status', 'accepted')
                ->sum('amount')
            : 0.0;

        return compact('total', 'pending', 'delivered', 'failed', 'cancelled', 'unassigned', 'successRate', 'collection');
    }

    public function getZones(): Collection
    {
        return Zone::orderBy('name')->get(['id', 'name']);
    }

    public function getStaffList(): Collection
    {
        return User::deliveryStaff()->orderBy('name')->get(['id', 'name']);
    }
}
