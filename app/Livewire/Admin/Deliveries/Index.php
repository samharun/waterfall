<?php

namespace App\Livewire\Admin\Deliveries;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $zone = '';
    public string $staff = '';
    public string $status = '';
    public ?string $assignedFrom = null;
    public ?string $assignedUntil = null;
    public ?string $deliveredFrom = null;
    public ?string $deliveredUntil = null;
    public bool $showTrashed = false;

    public bool $isFormOpen = false;
    public ?int $editingId = null;
    public string|int|null $order_id = null;
    public string|int|null $zone_id = null;
    public string|int|null $delivery_staff_id = null;
    public string $delivery_status = 'pending';
    public string|int|null $assigned_by = null;
    public ?string $assigned_at = null;
    public ?string $delivered_at = null;
    public ?string $delivery_note = null;
    public ?string $failure_reason = null;

    public bool $isAssignFormOpen = false;
    public ?int $assignDeliveryId = null;
    public string|int|null $assign_staff_id = null;

    public bool $isFailFormOpen = false;
    public ?int $failDeliveryId = null;
    public ?string $failReason = null;

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('deliveries.view'), 403);
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingZone(): void { $this->resetPage(); }
    public function updatingStaff(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingAssignedFrom(): void { $this->resetPage(); }
    public function updatingAssignedUntil(): void { $this->resetPage(); }
    public function updatingDeliveredFrom(): void { $this->resetPage(); }
    public function updatingDeliveredUntil(): void { $this->resetPage(); }
    public function updatingShowTrashed(): void { $this->resetPage(); }

    public function updatedOrderId($value): void
    {
        if (! $value) {
            return;
        }

        $order = Order::find($value);
        if ($order?->zone_id) {
            $this->zone_id = $order->zone_id;
        }
    }

    public function updatedDeliveryStaffId($value): void
    {
        if (! $value) {
            return;
        }

        if ($this->delivery_status === 'pending') {
            $this->delivery_status = 'assigned';
        }

        $this->assigned_at ??= now()->format('Y-m-d\TH:i');
        $this->assigned_by ??= Auth::id();
    }

    public function create(): void
    {
        $this->authorizePermission('deliveries.create');
        $this->resetForm();
        $this->isFormOpen = true;
    }

    public function edit(int $deliveryId): void
    {
        $this->authorizePermission('deliveries.update');

        $delivery = Delivery::withTrashed()->findOrFail($deliveryId);

        $this->editingId = $delivery->id;
        $this->order_id = $delivery->order_id;
        $this->zone_id = $delivery->zone_id;
        $this->delivery_staff_id = $delivery->delivery_staff_id;
        $this->delivery_status = $delivery->delivery_status;
        $this->assigned_by = $delivery->assigned_by;
        $this->assigned_at = $delivery->assigned_at?->format('Y-m-d\TH:i');
        $this->delivered_at = $delivery->delivered_at?->format('Y-m-d\TH:i');
        $this->delivery_note = $delivery->delivery_note;
        $this->failure_reason = $delivery->failure_reason;
        $this->isFormOpen = true;
    }

    public function save(): void
    {
        $this->authorizePermission($this->editingId ? 'deliveries.update' : 'deliveries.create');
        $this->normalizeNullableFields();

        $validated = $this->validate([
            'order_id' => ['required', 'integer', Rule::exists('orders', 'id')],
            'zone_id' => ['nullable', 'integer', Rule::exists('zones', 'id')],
            'delivery_staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'delivery_status' => ['required', Rule::in(array_keys(Delivery::statusLabels()))],
            'assigned_by' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'assigned_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date'],
            'delivery_note' => ['nullable', 'string'],
            'failure_reason' => [Rule::requiredIf($this->delivery_status === 'failed'), 'nullable', 'string'],
        ]);

        if (! empty($validated['delivery_staff_id']) && empty($validated['assigned_at'])) {
            $validated['assigned_at'] = now();
            $validated['assigned_by'] = $validated['assigned_by'] ?? Auth::id();
        }

        if (! empty($validated['delivery_staff_id']) && $validated['delivery_status'] === 'pending') {
            $validated['delivery_status'] = 'assigned';
        }

        if ($validated['delivery_status'] === 'delivered' && empty($validated['delivered_at'])) {
            $validated['delivered_at'] = now();
        }

        Delivery::withTrashed()->updateOrCreate(
            ['id' => $this->editingId],
            $validated,
        );

        session()->flash('success', $this->editingId ? 'Delivery updated successfully.' : 'Delivery created successfully.');

        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function openAssignForm(int $deliveryId): void
    {
        $this->authorizePermission('deliveries.update');
        $delivery = Delivery::findOrFail($deliveryId);

        $this->resetAssignForm();
        $this->assignDeliveryId = $delivery->id;
        $this->assign_staff_id = $delivery->delivery_staff_id;
        $this->isAssignFormOpen = true;
    }

    public function assignStaff(): void
    {
        $this->authorizePermission('deliveries.update');

        $validated = $this->validate([
            'assignDeliveryId' => ['required', 'integer', Rule::exists('deliveries', 'id')],
            'assign_staff_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        Delivery::findOrFail($validated['assignDeliveryId'])->markAssigned((int) $validated['assign_staff_id'], Auth::id());

        session()->flash('success', 'Delivery assigned.');
        $this->resetAssignForm();
        $this->isAssignFormOpen = false;
    }

    public function markInProgress(int $deliveryId): void
    {
        $this->authorizePermission('deliveries.update');
        Delivery::findOrFail($deliveryId)->markInProgress();
        session()->flash('success', 'Delivery marked in progress.');
    }

    public function markDelivered(int $deliveryId): void
    {
        $this->authorizePermission('deliveries.update');
        Delivery::findOrFail($deliveryId)->markDelivered();
        session()->flash('success', 'Delivery completed.');
    }

    public function openFailForm(int $deliveryId): void
    {
        $this->authorizePermission('deliveries.update');
        Delivery::findOrFail($deliveryId);

        $this->resetFailForm();
        $this->failDeliveryId = $deliveryId;
        $this->isFailFormOpen = true;
    }

    public function markFailed(): void
    {
        $this->authorizePermission('deliveries.update');

        $validated = $this->validate([
            'failDeliveryId' => ['required', 'integer', Rule::exists('deliveries', 'id')],
            'failReason' => ['required', 'string'],
        ]);

        Delivery::findOrFail($validated['failDeliveryId'])->markFailed($validated['failReason']);

        session()->flash('success', 'Delivery marked failed.');
        $this->resetFailForm();
        $this->isFailFormOpen = false;
    }

    public function markCancelled(int $deliveryId): void
    {
        $this->authorizePermission('deliveries.update');
        Delivery::findOrFail($deliveryId)->markCancelled();
        session()->flash('success', 'Delivery cancelled.');
    }

    public function delete(int $deliveryId): void
    {
        $this->authorizePermission('deliveries.delete');
        Delivery::findOrFail($deliveryId)->delete();
        session()->flash('success', 'Delivery moved to trash.');
    }

    public function restore(int $deliveryId): void
    {
        $this->authorizePermission('deliveries.delete');
        Delivery::onlyTrashed()->findOrFail($deliveryId)->restore();
        session()->flash('success', 'Delivery restored successfully.');
    }

    public function forceDelete(int $deliveryId): void
    {
        $this->authorizePermission('deliveries.delete');
        Delivery::onlyTrashed()->findOrFail($deliveryId)->forceDelete();
        session()->flash('success', 'Delivery permanently deleted.');
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function cancelAssign(): void
    {
        $this->resetAssignForm();
        $this->isAssignFormOpen = false;
    }

    public function cancelFail(): void
    {
        $this->resetFailForm();
        $this->isFailFormOpen = false;
    }

    public function render(): View
    {
        $deliveries = Delivery::query()
            ->with(['order.customer', 'order.dealer', 'zone', 'deliveryStaff'])
            ->when($this->showTrashed, fn ($query) => $query->withTrashed())
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('delivery_no', 'like', '%'.$this->search.'%')
                        ->orWhereHas('order', function ($orderQuery) {
                            $orderQuery->where('order_no', 'like', '%'.$this->search.'%')
                                ->orWhereHas('customer', function ($customerQuery) {
                                    $customerQuery->where('customer_id', 'like', '%'.$this->search.'%')
                                        ->orWhere('name', 'like', '%'.$this->search.'%')
                                        ->orWhere('mobile', 'like', '%'.$this->search.'%');
                                })
                                ->orWhereHas('dealer', function ($dealerQuery) {
                                    $dealerQuery->where('dealer_code', 'like', '%'.$this->search.'%')
                                        ->orWhere('name', 'like', '%'.$this->search.'%')
                                        ->orWhere('mobile', 'like', '%'.$this->search.'%');
                                });
                        });
                });
            })
            ->when($this->zone !== '', fn ($query) => $query->where('zone_id', $this->zone))
            ->when($this->staff !== '', fn ($query) => $query->where('delivery_staff_id', $this->staff))
            ->when($this->status !== '', fn ($query) => $query->where('delivery_status', $this->status))
            ->when($this->assignedFrom, fn ($query) => $query->whereDate('assigned_at', '>=', $this->assignedFrom))
            ->when($this->assignedUntil, fn ($query) => $query->whereDate('assigned_at', '<=', $this->assignedUntil))
            ->when($this->deliveredFrom, fn ($query) => $query->whereDate('delivered_at', '>=', $this->deliveredFrom))
            ->when($this->deliveredUntil, fn ($query) => $query->whereDate('delivered_at', '<=', $this->deliveredUntil))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.deliveries.index', [
            'deliveries' => $deliveries,
            'orders' => $this->orderOptions(),
            'zones' => Zone::orderBy('name')->pluck('name', 'id'),
            'activeZones' => Zone::active()->orderBy('name')->pluck('name', 'id'),
            'deliveryStaff' => $this->deliveryStaffOptions(),
            'backOfficeUsers' => User::backOffice()->orderBy('name')->pluck('name', 'id'),
            'statusLabels' => Delivery::statusLabels(),
            'permissions' => [
                'create' => auth()->user()?->can('deliveries.create') ?? false,
                'update' => auth()->user()?->can('deliveries.update') ?? false,
                'delete' => auth()->user()?->can('deliveries.delete') ?? false,
            ],
        ])->layout('admin.layouts.app', ['title' => 'Delivery Assignments']);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }

    private function normalizeNullableFields(): void
    {
        foreach (['zone_id', 'delivery_staff_id', 'assigned_by', 'assigned_at', 'delivered_at', 'delivery_note', 'failure_reason'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->order_id = null;
        $this->zone_id = null;
        $this->delivery_staff_id = null;
        $this->delivery_status = 'pending';
        $this->assigned_by = Auth::id();
        $this->assigned_at = null;
        $this->delivered_at = null;
        $this->delivery_note = null;
        $this->failure_reason = null;
    }

    private function resetAssignForm(): void
    {
        $this->resetValidation();
        $this->assignDeliveryId = null;
        $this->assign_staff_id = null;
    }

    private function resetFailForm(): void
    {
        $this->resetValidation();
        $this->failDeliveryId = null;
        $this->failReason = null;
    }

    private function orderOptions()
    {
        return Order::with(['customer', 'dealer'])
            ->where(function ($query) {
                $query->whereIn('order_status', ['confirmed', 'assigned'])
                    ->when($this->order_id, fn ($query) => $query->orWhere('id', $this->order_id));
            })
            ->get()
            ->filter(fn (Order $order) => $this->editingId || $order->canCreateDelivery())
            ->mapWithKeys(fn (Order $order) => [
                $order->id => $this->orderLabel($order),
            ]);
    }

    private function deliveryStaffOptions()
    {
        return User::deliveryStaff()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user) => [
                $user->id => "{$user->name} ({$user->email})",
            ]);
    }

    private function orderLabel(Order $order): string
    {
        $party = match ($order->order_type) {
            'customer' => $order->customer ? "{$order->customer->customer_id} {$order->customer->name}" : 'Customer',
            'dealer' => $order->dealer ? "{$order->dealer->dealer_code} {$order->dealer->name}" : 'Dealer',
            default => 'Unknown',
        };

        return "{$order->order_no} - {$party} (BDT {$order->total_amount})";
    }
}
