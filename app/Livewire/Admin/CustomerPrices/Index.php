<?php

namespace App\Livewire\Admin\CustomerPrices;

use App\Models\Customer;
use App\Models\CustomerPrice;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $customer = '';
    public string $product = '';
    public string $status = '';
    public bool $currentlyEffective = false;
    public bool $showTrashed = false;

    public bool $isFormOpen = false;
    public ?int $editingId = null;
    public string|int|null $customer_id = null;
    public string|int|null $product_id = null;
    public string|float|int $custom_price = 0;
    public ?string $effective_from = null;
    public ?string $effective_to = null;
    public string $formStatus = 'active';

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('customer_prices.view'), 403);
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

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCurrentlyEffective(): void
    {
        $this->resetPage();
    }

    public function updatingShowTrashed(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorizeManage();
        $this->resetForm();
        $this->isFormOpen = true;
    }

    public function edit(int $customerPriceId): void
    {
        $this->authorizeManage();

        $customerPrice = CustomerPrice::withTrashed()->findOrFail($customerPriceId);

        $this->editingId = $customerPrice->id;
        $this->customer_id = $customerPrice->customer_id;
        $this->product_id = $customerPrice->product_id;
        $this->custom_price = $customerPrice->custom_price;
        $this->effective_from = $customerPrice->effective_from?->toDateString();
        $this->effective_to = $customerPrice->effective_to?->toDateString();
        $this->formStatus = $customerPrice->status;
        $this->isFormOpen = true;
    }

    public function save(): void
    {
        $this->authorizeManage();
        $this->normalizeNullableFields();

        $validated = $this->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'custom_price' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'formStatus' => ['required', Rule::in(array_keys(CustomerPrice::statusLabels()))],
        ]);

        CustomerPrice::withTrashed()->updateOrCreate(
            ['id' => $this->editingId],
            [
                'customer_id' => $validated['customer_id'],
                'product_id' => $validated['product_id'],
                'custom_price' => $validated['custom_price'],
                'effective_from' => $validated['effective_from'],
                'effective_to' => $validated['effective_to'],
                'status' => $validated['formStatus'],
            ],
        );

        session()->flash('success', $this->editingId ? 'Customer price updated successfully.' : 'Customer price created successfully.');

        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function delete(int $customerPriceId): void
    {
        $this->authorizeManage();

        CustomerPrice::findOrFail($customerPriceId)->delete();
        session()->flash('success', 'Customer price moved to trash.');
    }

    public function restore(int $customerPriceId): void
    {
        $this->authorizeManage();

        CustomerPrice::onlyTrashed()->findOrFail($customerPriceId)->restore();
        session()->flash('success', 'Customer price restored successfully.');
    }

    public function forceDelete(int $customerPriceId): void
    {
        $this->authorizeManage();

        CustomerPrice::onlyTrashed()->findOrFail($customerPriceId)->forceDelete();
        session()->flash('success', 'Customer price permanently deleted.');
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function render(): View
    {
        $customerPrices = CustomerPrice::query()
            ->with(['customer', 'product'])
            ->when($this->showTrashed, fn ($query) => $query->withTrashed())
            ->when($this->currentlyEffective, fn ($query) => $query->currentlyEffective())
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('customer', function ($customerQuery) {
                        $customerQuery->where('customer_id', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%')
                            ->orWhere('mobile', 'like', '%'.$this->search.'%');
                    })->orWhereHas('product', function ($productQuery) {
                        $productQuery->where('sku', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%');
                    });
                });
            })
            ->when($this->customer !== '', fn ($query) => $query->where('customer_id', $this->customer))
            ->when($this->product !== '', fn ($query) => $query->where('product_id', $this->product))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.customer-prices.index', [
            'customerPrices' => $customerPrices,
            'customers' => $this->customerOptions(),
            'products' => $this->productOptions(),
            'statusLabels' => CustomerPrice::statusLabels(),
            'canManage' => auth()->user()?->can('customer_prices.manage') ?? false,
        ])->layout('admin.layouts.app', ['title' => 'Customer Pricing']);
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('customer_prices.manage'), 403);
    }

    private function normalizeNullableFields(): void
    {
        foreach (['effective_from', 'effective_to'] as $field) {
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
        $this->custom_price = 0;
        $this->effective_from = null;
        $this->effective_to = null;
        $this->formStatus = 'active';
    }

    private function customerOptions()
    {
        return Customer::orderBy('name')
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
}
