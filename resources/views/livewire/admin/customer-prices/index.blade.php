<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">Customer Pricing</h1>
            <p class="page-subtitle">Manage custom product prices per customer with effective date ranges.</p>
        </div>

        @if($canManage)
            <button type="button" class="btn btn-primary" wire:click="create">New Customer Price</button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($isFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">{{ $editingId ? 'Edit Customer Price' : 'Create Customer Price' }}</h2>
                    <p class="panel-subtitle">Leave dates empty when the custom price has no date boundary.</p>
                </div>
            </div>

            <form wire:submit="save" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="price-customer">Customer</label>
                    <select id="price-customer" class="form-control" wire:model="customer_id">
                        <option value="">Select customer</option>
                        @foreach($customers as $id => $customerLabel)
                            <option value="{{ $id }}">{{ $customerLabel }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="price-product">Product</label>
                    <select id="price-product" class="form-control" wire:model="product_id">
                        <option value="">Select product</option>
                        @foreach($products as $id => $productLabel)
                            <option value="{{ $id }}">{{ $productLabel }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="custom-price">Custom Price</label>
                    <input id="custom-price" class="form-control" type="number" step="0.01" min="0" wire:model.blur="custom_price">
                    @error('custom_price') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="price-status">Status</label>
                    <select id="price-status" class="form-control" wire:model="formStatus">
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('formStatus') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="effective-from">Effective From</label>
                    <input id="effective-from" class="form-control" type="date" wire:model="effective_from">
                    @error('effective_from') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="effective-to">Effective To</label>
                    <input id="effective-to" class="form-control" type="date" wire:model="effective_to">
                    @error('effective_to') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Price</button>
                    <button type="button" class="btn btn-muted" wire:click="cancel">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    <section class="panel">
        <div class="toolbar">
            <input type="search" class="form-control search-input" placeholder="Search customer, mobile, product, or SKU" wire:model.live.debounce.300ms="search">

            <select class="form-control filter-control" wire:model.live="customer">
                <option value="">All customers</option>
                @foreach($customers as $id => $customerLabel)
                    <option value="{{ $id }}">{{ $customerLabel }}</option>
                @endforeach
            </select>

            <select class="form-control filter-control" wire:model.live="product">
                <option value="">All products</option>
                @foreach($products as $id => $productLabel)
                    <option value="{{ $id }}">{{ $productLabel }}</option>
                @endforeach
            </select>

            <select class="form-control filter-control" wire:model.live="status">
                <option value="">All statuses</option>
                @foreach($statusLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <label class="check-control">
                <input type="checkbox" wire:model.live="currentlyEffective">
                <span>Currently effective</span>
            </label>

            <label class="check-control">
                <input type="checkbox" wire:model.live="showTrashed">
                <span>Show trashed</span>
            </label>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Mobile</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Effective</th>
                        <th>Status</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerPrices as $customerPrice)
                        <tr @class(['is-muted' => $customerPrice->trashed()])>
                            <td>
                                <div><span class="mono">{{ $customerPrice->customer?->customer_id ?? '-' }}</span></div>
                                <strong>{{ $customerPrice->customer?->name ?? 'Deleted customer' }}</strong>
                                @if($customerPrice->trashed())
                                    <span class="badge badge-muted">Trashed</span>
                                @endif
                            </td>
                            <td>{{ $customerPrice->customer?->mobile ?? '-' }}</td>
                            <td>
                                <div><span class="mono">{{ $customerPrice->product?->sku ?? '-' }}</span></div>
                                <strong>{{ $customerPrice->product?->name ?? 'Deleted product' }}</strong>
                            </td>
                            <td>BDT {{ number_format((float) $customerPrice->custom_price, 2) }}</td>
                            <td>
                                <div>From: {{ $customerPrice->effective_from?->format('Y-m-d') ?? '-' }}</div>
                                <div class="text-muted">To: {{ $customerPrice->effective_to?->format('Y-m-d') ?? '-' }}</div>
                            </td>
                            <td>
                                <span @class(['badge', 'badge-success' => $customerPrice->status === 'active', 'badge-muted' => $customerPrice->status !== 'active'])>
                                    {{ $statusLabels[$customerPrice->status] ?? ucfirst($customerPrice->status) }}
                                </span>
                            </td>
                            <td>
                                @if($canManage)
                                    <div class="row-actions">
                                        @if($customerPrice->trashed())
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="restore({{ $customerPrice->id }})">Restore</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="forceDelete({{ $customerPrice->id }})" wire:confirm="Permanently delete this price?">Delete Forever</button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="edit({{ $customerPrice->id }})">Edit</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="delete({{ $customerPrice->id }})" wire:confirm="Move this price to trash?">Delete</button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><div class="empty-state">No customer prices found.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $customerPrices->links() }}
        </div>
    </section>
</div>
