<?php

namespace App\Livewire\Admin\DealerPrices;

use App\Models\Dealer;
use App\Models\DealerPrice;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dealer = '';
    public string $product = '';
    public string $status = '';
    public bool $currentlyEffective = false;
    public bool $showTrashed = false;

    public bool $isFormOpen = false;
    public ?int $editingId = null;
    public string|int|null $dealer_id = null;
    public string|int|null $product_id = null;
    public string|float|int $custom_price = 0;
    public ?string $effective_from = null;
    public ?string $effective_to = null;
    public string $formStatus = 'active';

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('dealer_prices.view'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDealer(): void
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

    public function edit(int $dealerPriceId): void
    {
        $this->authorizeManage();

        $dealerPrice = DealerPrice::withTrashed()->findOrFail($dealerPriceId);

        $this->editingId = $dealerPrice->id;
        $this->dealer_id = $dealerPrice->dealer_id;
        $this->product_id = $dealerPrice->product_id;
        $this->custom_price = $dealerPrice->custom_price;
        $this->effective_from = $dealerPrice->effective_from?->toDateString();
        $this->effective_to = $dealerPrice->effective_to?->toDateString();
        $this->formStatus = $dealerPrice->status;
        $this->isFormOpen = true;
    }

    public function save(): void
    {
        $this->authorizeManage();
        $this->normalizeNullableFields();

        $validated = $this->validate([
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'custom_price' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'formStatus' => ['required', Rule::in(array_keys(DealerPrice::statusLabels()))],
        ]);

        DealerPrice::withTrashed()->updateOrCreate(
            ['id' => $this->editingId],
            [
                'dealer_id' => $validated['dealer_id'],
                'product_id' => $validated['product_id'],
                'custom_price' => $validated['custom_price'],
                'effective_from' => $validated['effective_from'],
                'effective_to' => $validated['effective_to'],
                'status' => $validated['formStatus'],
            ],
        );

        session()->flash('success', $this->editingId ? 'Dealer price updated successfully.' : 'Dealer price created successfully.');

        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function delete(int $dealerPriceId): void
    {
        $this->authorizeManage();

        DealerPrice::findOrFail($dealerPriceId)->delete();
        session()->flash('success', 'Dealer price moved to trash.');
    }

    public function restore(int $dealerPriceId): void
    {
        $this->authorizeManage();

        DealerPrice::onlyTrashed()->findOrFail($dealerPriceId)->restore();
        session()->flash('success', 'Dealer price restored successfully.');
    }

    public function forceDelete(int $dealerPriceId): void
    {
        $this->authorizeManage();

        DealerPrice::onlyTrashed()->findOrFail($dealerPriceId)->forceDelete();
        session()->flash('success', 'Dealer price permanently deleted.');
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function render(): View
    {
        $dealerPrices = DealerPrice::query()
            ->with(['dealer', 'product'])
            ->when($this->showTrashed, fn ($query) => $query->withTrashed())
            ->when($this->currentlyEffective, fn ($query) => $query->currentlyEffective())
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('dealer', function ($dealerQuery) {
                        $dealerQuery->where('dealer_code', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%')
                            ->orWhere('mobile', 'like', '%'.$this->search.'%');
                    })->orWhereHas('product', function ($productQuery) {
                        $productQuery->where('sku', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%');
                    });
                });
            })
            ->when($this->dealer !== '', fn ($query) => $query->where('dealer_id', $this->dealer))
            ->when($this->product !== '', fn ($query) => $query->where('product_id', $this->product))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.dealer-prices.index', [
            'dealerPrices' => $dealerPrices,
            'dealers' => $this->dealerOptions(),
            'products' => $this->productOptions(),
            'statusLabels' => DealerPrice::statusLabels(),
            'canManage' => auth()->user()?->can('dealer_prices.manage') ?? false,
        ])->layout('admin.layouts.app', ['title' => 'Dealer Pricing']);
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('dealer_prices.manage'), 403);
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
        $this->dealer_id = null;
        $this->product_id = null;
        $this->custom_price = 0;
        $this->effective_from = null;
        $this->effective_to = null;
        $this->formStatus = 'active';
    }

    private function dealerOptions()
    {
        return Dealer::orderBy('name')
            ->get()
            ->mapWithKeys(fn (Dealer $dealer) => [
                $dealer->id => "{$dealer->dealer_code} - {$dealer->name} ({$dealer->mobile})",
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
