<?php

namespace App\Filament\Admin\Pages;

use App\Models\Order;
use App\Models\Zone;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OrdersOverview extends Page
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'orders.view';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string|\UnitEnum|null $navigationGroup = 'Order & Delivery';
    protected static ?string $navigationLabel = 'Orders Overview';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.admin.pages.orders-overview';

    // ── Filter state ───────────────────────────────────────────────
    public ?string $date_from      = null;
    public ?string $date_until     = null;
    public ?string $order_status   = null;
    public ?string $order_type     = null;
    public ?string $payment_status = null;
    public ?int    $zone_id        = null;

    public function mount(): void
    {
        $this->date_from  = Carbon::now()->startOfMonth()->toDateString();
        $this->date_until = Carbon::now()->toDateString();
    }

    public function getOrders(): Collection
    {
        return Order::with(['customer', 'dealer', 'zone'])
            ->when($this->date_from,      fn ($q) => $q->whereDate('order_date', '>=', $this->date_from))
            ->when($this->date_until,     fn ($q) => $q->whereDate('order_date', '<=', $this->date_until))
            ->when($this->order_status,   fn ($q) => $q->where('order_status', $this->order_status))
            ->when($this->order_type,     fn ($q) => $q->where('order_type', $this->order_type))
            ->when($this->payment_status, fn ($q) => $q->where('payment_status', $this->payment_status))
            ->when($this->zone_id,        fn ($q) => $q->where('zone_id', $this->zone_id))
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->limit(300)
            ->get();
    }

    public function getStats(): array
    {
        $orders    = $this->getOrders();
        $total     = $orders->count();
        $pending   = $orders->where('order_status', 'pending')->count();
        $confirmed = $orders->where('order_status', 'confirmed')->count();
        $delivered = $orders->where('order_status', 'delivered')->count();
        $cancelled = $orders->where('order_status', 'cancelled')->count();
        $revenue   = (float) $orders->sum('total_amount');
        $unpaid    = $orders->where('payment_status', 'unpaid')->count();

        return compact('total', 'pending', 'confirmed', 'delivered', 'cancelled', 'revenue', 'unpaid');
    }

    public function getZones(): Collection
    {
        return Zone::orderBy('name')->get(['id', 'name']);
    }
}
