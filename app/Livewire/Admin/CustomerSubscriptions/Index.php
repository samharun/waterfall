<?php

namespace App\Livewire\Admin\CustomerSubscriptions;

use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $customer = '';
    public string $product = '';
    public string $frequency = '';
    public string $status = '';
    public bool $dueForDelivery = false;
    public bool $showTrashed = false;

    public bool $isFormOpen = false;
    public ?int $editingId = null;
    public string|int|null $customer_id = null;
    public string|int|null $product_id = null;
    public string|int $quantity = 1;
    public string $formFrequency = 'daily';
    public array $delivery_days = [];
    public string $preferred_delivery_slot = 'morning';
    public ?string $preferred_delivery_time = null;
    public ?string $start_date = null;
    public ?string $next_delivery_date = null;
    public ?string $paused_from = null;
    public ?string $paused_to = null;
    public ?string $pause_reason = null;
    public string $formStatus = 'active';
    public ?string $remarks = null;

    public bool $isPauseFormOpen = false;
    public ?int $pauseSubscriptionId = null;
    public ?string $pause_from = null;
    public ?string $pause_to = null;
    public ?string $pauseReason = null;

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('subscriptions.view'), 403);
        $this->start_date = today()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCustomer(): void
    {
        $this->resetPage();
    }

    public function updatingProduct(): void
    {
        $this->resetPage();
    }

    public function updatingFrequency(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDueForDelivery(): void
    {
        $this->resetPage();
    }

    public function updatingShowTrashed(): void
    {
        $this->resetPage();
    }

    public function updatedFormFrequency(): void
    {
        if (! in_array($this->formFrequency, ['weekly', 'custom_days'], true)) {
            $this->delivery_days = [];
        }
    }

    public function updatedPreferredDeliverySlot(): void
    {
        if ($this->preferred_delivery_slot !== 'custom') {
            $this->preferred_delivery_time = null;
        }
    }

    public function create(): void
    {
        $this->authorizeManage();
        $this->resetForm();
        $this->isFormOpen = true;
    }

    public function edit(int $subscriptionId): void
    {
        $this->authorizeManage();

        $subscription = CustomerSubscription::withTrashed()->findOrFail($subscriptionId);

        $this->editingId = $subscription->id;
        $this->customer_id = $subscription->customer_id;
        $this->product_id = $subscription->product_id;
        $this->quantity = $subscription->quantity;
        $this->formFrequency = $subscription->frequency;
        $this->delivery_days = $subscription->delivery_days ?? [];
        $this->preferred_delivery_slot = $subscription->preferred_delivery_slot;
        $this->preferred_delivery_time = $subscription->preferred_delivery_time
            ? substr((string) $subscription->preferred_delivery_time, 0, 5)
            : null;
        $this->start_date = $subscription->start_date?->toDateString();
        $this->next_delivery_date = $subscription->next_delivery_date?->toDateString();
        $this->paused_from = $subscription->paused_from?->toDateString();
        $this->paused_to = $subscription->paused_to?->toDateString();
        $this->pause_reason = $subscription->pause_reason;
        $this->formStatus = $subscription->status;
        $this->remarks = $subscription->remarks;
        $this->isFormOpen = true;
    }

    public function save(): void
    {
        $this->authorizeManage();
        $this->normalizeNullableFields();

        $validated = $this->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'quantity' => ['required', 'integer', 'min:1'],
            'formFrequency' => ['required', Rule::in(array_keys(CustomerSubscription::frequencyLabels()))],
            'delivery_days' => [Rule::requiredIf(in_array($this->formFrequency, ['weekly', 'custom_days'], true)), 'array'],
            'delivery_days.*' => [Rule::in(array_keys(CustomerSubscription::deliveryDayOptions()))],
            'preferred_delivery_slot' => ['required', Rule::in(array_keys(CustomerSubscription::slotLabels()))],
            'preferred_delivery_time' => [Rule::requiredIf($this->preferred_delivery_slot === 'custom'), 'nullable', 'date_format:H:i'],
            'start_date' => ['required', 'date'],
            'next_delivery_date' => ['nullable', 'date'],
            'paused_from' => ['nullable', 'date'],
            'paused_to' => ['nullable', 'date', 'after_or_equal:paused_from'],
            'pause_reason' => ['nullable', 'string'],
            'formStatus' => ['required', Rule::in(array_keys(CustomerSubscription::statusLabels()))],
            'remarks' => ['nullable', 'string'],
        ]);

        $subscription = CustomerSubscription::withTrashed()->updateOrCreate(
            ['id' => $this->editingId],
            [
                'customer_id' => $validated['customer_id'],
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'frequency' => $validated['formFrequency'],
                'delivery_days' => in_array($validated['formFrequency'], ['weekly', 'custom_days'], true) ? $validated['delivery_days'] : null,
                'preferred_delivery_slot' => $validated['preferred_delivery_slot'],
                'preferred_delivery_time' => $validated['preferred_delivery_time'],
                'start_date' => $validated['start_date'],
                'next_delivery_date' => $validated['next_delivery_date'],
                'paused_from' => $validated['paused_from'],
                'paused_to' => $validated['paused_to'],
                'pause_reason' => $validated['pause_reason'],
                'status' => $validated['formStatus'],
                'remarks' => $validated['remarks'],
                'created_by' => $this->editingId ? CustomerSubscription::withTrashed()->find($this->editingId)?->created_by : Auth::id(),
                'updated_by' => Auth::id(),
            ],
        );

        if (! $subscription->next_delivery_date) {
            $next = $subscription->calculateNextDeliveryDate();
            $subscription->update(['next_delivery_date' => $next?->toDateString()]);
        }

        session()->flash('success', $this->editingId ? 'Subscription updated successfully.' : 'Subscription created successfully.');

        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function openPauseForm(int $subscriptionId): void
    {
        $this->authorizeManage();

        $subscription = CustomerSubscription::findOrFail($subscriptionId);

        $this->resetPauseForm();
        $this->pauseSubscriptionId = $subscription->id;
        $this->pause_from = today()->toDateString();
        $this->isPauseFormOpen = true;
    }

    public function pause(): void
    {
        $this->authorizeManage();

        $validated = $this->validate([
            'pauseSubscriptionId' => ['required', 'integer', Rule::exists('customer_subscriptions', 'id')],
            'pause_from' => ['required', 'date'],
            'pause_to' => ['nullable', 'date', 'after_or_equal:pause_from'],
            'pauseReason' => ['nullable', 'string'],
        ]);

        CustomerSubscription::findOrFail($validated['pauseSubscriptionId'])
            ->pause($validated['pauseReason'], $validated['pause_from'], $validated['pause_to']);

        session()->flash('success', 'Subscription paused.');
        $this->resetPauseForm();
        $this->isPauseFormOpen = false;
    }

    public function resume(int $subscriptionId): void
    {
        $this->authorizeManage();

        CustomerSubscription::findOrFail($subscriptionId)->resume();
        session()->flash('success', 'Subscription resumed.');
    }

    public function cancelSubscription(int $subscriptionId): void
    {
        $this->authorizeManage();

        CustomerSubscription::findOrFail($subscriptionId)->cancel();
        session()->flash('success', 'Subscription cancelled.');
    }

    public function recalculateNextDate(int $subscriptionId): void
    {
        $this->authorizeManage();

        $subscription = CustomerSubscription::findOrFail($subscriptionId);
        $next = $subscription->calculateNextDeliveryDate();
        $subscription->update(['next_delivery_date' => $next?->toDateString()]);

        session()->flash('success', 'Next delivery date updated.');
    }

    public function delete(int $subscriptionId): void
    {
        $this->authorizeManage();

        CustomerSubscription::findOrFail($subscriptionId)->delete();
        session()->flash('success', 'Subscription moved to trash.');
    }

    public function restore(int $subscriptionId): void
    {
        $this->authorizeManage();

        CustomerSubscription::onlyTrashed()->findOrFail($subscriptionId)->restore();
        session()->flash('success', 'Subscription restored successfully.');
    }

    public function forceDelete(int $subscriptionId): void
    {
        $this->authorizeManage();

        CustomerSubscription::onlyTrashed()->findOrFail($subscriptionId)->forceDelete();
        session()->flash('success', 'Subscription permanently deleted.');
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function cancelPause(): void
    {
        $this->resetPauseForm();
        $this->isPauseFormOpen = false;
    }

    public function render(): View
    {
        $subscriptions = CustomerSubscription::query()
            ->with(['customer', 'product'])
            ->when($this->showTrashed, fn ($query) => $query->withTrashed())
            ->when($this->dueForDelivery, fn ($query) => $query->dueForDelivery())
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('subscription_no', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($customerQuery) {
                            $customerQuery->where('customer_id', 'like', '%'.$this->search.'%')
                                ->orWhere('name', 'like', '%'.$this->search.'%')
                                ->orWhere('mobile', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->customer !== '', fn ($query) => $query->where('customer_id', $this->customer))
            ->when($this->product !== '', fn ($query) => $query->where('product_id', $this->product))
            ->when($this->frequency !== '', fn ($query) => $query->where('frequency', $this->frequency))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.customer-subscriptions.index', [
            'subscriptions' => $subscriptions,
            'customers' => $this->customerOptions(),
            'products' => $this->productOptions(),
            'frequencyLabels' => CustomerSubscription::frequencyLabels(),
            'statusLabels' => CustomerSubscription::statusLabels(),
            'deliveryDayOptions' => CustomerSubscription::deliveryDayOptions(),
            'slotLabels' => CustomerSubscription::slotLabels(),
            'stats' => $this->stats(),
            'canManage' => auth()->user()?->can('subscriptions.manage') ?? false,
        ])->layout('admin.layouts.app', ['title' => 'Subscriptions']);
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('subscriptions.manage'), 403);
    }

    private function normalizeNullableFields(): void
    {
        foreach (['preferred_delivery_time', 'next_delivery_date', 'paused_from', 'paused_to', 'pause_reason', 'remarks'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->customer_id = null;
        $this->product_id = null;
        $this->quantity = 1;
        $this->formFrequency = 'daily';
        $this->delivery_days = [];
        $this->preferred_delivery_slot = 'morning';
        $this->preferred_delivery_time = null;
        $this->start_date = today()->toDateString();
        $this->next_delivery_date = null;
        $this->paused_from = null;
        $this->paused_to = null;
        $this->pause_reason = null;
        $this->formStatus = 'active';
        $this->remarks = null;
    }

    private function resetPauseForm(): void
    {
        $this->resetValidation();
        $this->pauseSubscriptionId = null;
        $this->pause_from = null;
        $this->pause_to = null;
        $this->pauseReason = null;
    }

    private function customerOptions()
    {
        return Customer::approved()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Customer $customer) => [
                $customer->id => "{$customer->customer_id} - {$customer->name} ({$customer->mobile})",
            ]);
    }

    private function productOptions()
    {
        return Product::active()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Product $product) => [
                $product->id => "[{$product->sku}] {$product->name}",
            ]);
    }

    private function stats(): array
    {
        return [
            'total' => CustomerSubscription::count(),
            'active' => CustomerSubscription::active()->count(),
            'due' => CustomerSubscription::dueForDelivery()->count(),
            'paused' => CustomerSubscription::paused()->count(),
        ];
    }
}
