<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Traits\HasPagePermission;
use App\Models\Order;
use App\Models\User;
use App\Models\Zone;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrdersOverview extends Page
{
    use HasPagePermission;

    protected static string $accessPermission = 'orders.view';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|\UnitEnum|null $navigationGroup = 'Order & Delivery';

    protected static ?string $navigationLabel = 'Orders Overview';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.admin.pages.orders-overview';

    // ── Filter state ───────────────────────────────────────────────
    public ?string $date_from = null;

    public ?string $date_until = null;

    public ?string $order_status = null;

    public ?string $order_type = null;

    public ?string $payment_status = null;

    public ?int $zone_id = null;

    public bool $isAssignmentOpen = false;

    public ?int $assignmentOrderId = null;

    public ?int $assignmentZoneId = null;

    public ?int $assignmentStaffId = null;

    public string $assignmentOrderLabel = '';

    public string $assignmentZoneLabel = '';

    public function mount(): void
    {
        $this->date_from = Carbon::now()->startOfMonth()->toDateString();
        $this->date_until = Carbon::now()->toDateString();
    }

    public function getOrders(): Collection
    {
        return Order::with(['customer', 'dealer', 'zone', 'deliveries.deliveryStaff'])
            ->when($this->date_from, fn ($q) => $q->whereDate('order_date', '>=', $this->date_from))
            ->when($this->date_until, fn ($q) => $q->whereDate('order_date', '<=', $this->date_until))
            ->when($this->order_status, fn ($q) => $q->where('order_status', $this->order_status))
            ->when($this->order_type, fn ($q) => $q->where('order_type', $this->order_type))
            ->when($this->payment_status, fn ($q) => $q->where('payment_status', $this->payment_status))
            ->when($this->zone_id, fn ($q) => $q->where('zone_id', $this->zone_id))
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->limit(300)
            ->get();
    }

    public function getStats(): array
    {
        $orders = $this->getOrders();
        $total = $orders->count();
        $pending = $orders->where('order_status', 'pending')->count();
        $confirmed = $orders->where('order_status', 'confirmed')->count();
        $delivered = $orders->where('order_status', 'delivered')->count();
        $cancelled = $orders->where('order_status', 'cancelled')->count();
        $revenue = (float) $orders->sum('total_amount');
        $unpaid = $orders->where('payment_status', 'unpaid')->count();

        return compact('total', 'pending', 'confirmed', 'delivered', 'cancelled', 'revenue', 'unpaid');
    }

    public function getZones(): Collection
    {
        return Zone::orderBy('name')->get(['id', 'name']);
    }

    public function confirmOrder(int $orderId): void
    {
        abort_unless(auth()->user()?->can('orders.update'), 403);

        $order = Order::find($orderId);

        if (! $order || $order->order_status !== 'pending') {
            Notification::make()
                ->title('Order cannot be confirmed')
                ->body('The order was not found or is no longer pending.')
                ->warning()
                ->send();

            return;
        }

        $order->markConfirmed();

        if (auth()->user()?->can('deliveries.assign')) {
            $this->openAssignment($order->id);
        }

        Notification::make()
            ->title('Order confirmed successfully')
            ->body(auth()->user()?->can('deliveries.assign')
                ? 'Select a delivery staff member to complete the assignment.'
                : 'A pending delivery has been created and is ready for assignment.')
            ->success()
            ->send();
    }

    public function openAssignment(int $orderId): void
    {
        abort_unless(auth()->user()?->can('deliveries.assign'), 403);

        $order = Order::with(['customer.zone', 'dealer.zone', 'zone', 'deliveries'])->find($orderId);
        $delivery = $order?->deliveries
            ->first(fn ($item) => $item->delivery_status === 'pending');

        if (! $order || ! $delivery) {
            Notification::make()
                ->title('Delivery is not available for assignment')
                ->body('Confirm the order first, or check whether it has already been assigned.')
                ->warning()
                ->send();

            return;
        }

        $party = $order->customer ?? $order->dealer;
        $zone = $order->zone ?? $party?->zone;

        $this->resetValidation();
        $this->assignmentOrderId = $order->id;
        $this->assignmentZoneId = $zone?->id;
        $this->assignmentStaffId = null;
        $this->assignmentOrderLabel = $order->order_no.' — '.($party?->name ?? 'Customer');
        $this->assignmentZoneLabel = $zone?->name ?? 'No zone assigned';
        $this->isAssignmentOpen = true;
    }

    public function closeAssignment(): void
    {
        $this->resetAssignment();
    }

    public function updatedAssignmentZoneId(): void
    {
        $this->assignmentStaffId = null;
    }

    public function assignDeliveryStaff(): void
    {
        abort_unless(auth()->user()?->can('deliveries.assign'), 403);

        $validated = $this->validate([
            'assignmentOrderId' => ['required', 'integer', Rule::exists('orders', 'id')],
            'assignmentStaffId' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'delivery_staff')),
            ],
        ]);

        $order = Order::with('deliveries')->findOrFail($validated['assignmentOrderId']);
        $delivery = $order->deliveries
            ->first(fn ($item) => $item->delivery_status === 'pending');

        if (! $delivery) {
            $this->resetAssignment();

            Notification::make()
                ->title('Delivery is no longer pending')
                ->warning()
                ->send();

            return;
        }

        $delivery->markAssigned((int) $validated['assignmentStaffId'], Auth::id());

        $staff = User::find($validated['assignmentStaffId']);
        $this->resetAssignment();

        Notification::make()
            ->title('Delivery staff assigned successfully')
            ->body(($staff?->name ?? 'Selected staff').' is now assigned to '.$order->order_no.'.')
            ->success()
            ->send();
    }

    public function getAssignmentZones(): Collection
    {
        return Zone::active()->orderBy('name')->get(['id', 'name']);
    }

    public function getAssignmentStaffGroups(): array
    {
        $staff = User::deliveryStaff()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'mobile']);

        if (! $this->assignmentZoneId) {
            return [
                'zone' => collect(),
                'other' => $staff,
            ];
        }

        $zoneStaffIds = User::deliveryStaff()
            ->where(function ($query): void {
                $query
                    ->whereHas('zones', fn ($zoneQuery) => $zoneQuery->whereKey($this->assignmentZoneId))
                    ->orWhereHas('assignedDeliveries', fn ($deliveryQuery) => $deliveryQuery->where('zone_id', $this->assignmentZoneId));
            })
            ->pluck('id');

        return [
            'zone' => $staff->whereIn('id', $zoneStaffIds)->values(),
            'other' => $staff->whereNotIn('id', $zoneStaffIds)->values(),
        ];
    }

    private function resetAssignment(): void
    {
        $this->resetValidation();
        $this->isAssignmentOpen = false;
        $this->assignmentOrderId = null;
        $this->assignmentZoneId = null;
        $this->assignmentStaffId = null;
        $this->assignmentOrderLabel = '';
        $this->assignmentZoneLabel = '';
    }
}
