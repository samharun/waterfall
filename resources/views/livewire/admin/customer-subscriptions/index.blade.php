<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">Subscriptions</h1>
            <p class="page-subtitle">Manage recurring customer deliveries, pauses, cancellations, and next delivery dates.</p>
        </div>

        @if($canManage)
            <button type="button" class="btn btn-primary" wire:click="create">New Subscription</button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="metric-grid">
        <div class="metric-card">
            <span>Subscriptions</span>
            <strong>{{ number_format($stats['total']) }}</strong>
        </div>
        <div class="metric-card">
            <span>Active</span>
            <strong>{{ number_format($stats['active']) }}</strong>
        </div>
        <div class="metric-card">
            <span>Due Today</span>
            <strong>{{ number_format($stats['due']) }}</strong>
        </div>
        <div class="metric-card">
            <span>Paused</span>
            <strong>{{ number_format($stats['paused']) }}</strong>
        </div>
    </div>

    @if($isFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">{{ $editingId ? 'Edit Subscription' : 'Create Subscription' }}</h2>
                    <p class="panel-subtitle">Next delivery date is calculated automatically if left empty.</p>
                </div>
            </div>

            <form wire:submit="save" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="subscription-customer">Customer</label>
                    <select id="subscription-customer" class="form-control" wire:model="customer_id">
                        <option value="">Select customer</option>
                        @foreach($customers as $id => $customerLabel)
                            <option value="{{ $id }}">{{ $customerLabel }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="subscription-product">Product</label>
                    <select id="subscription-product" class="form-control" wire:model="product_id">
                        <option value="">Select product</option>
                        @foreach($products as $id => $productLabel)
                            <option value="{{ $id }}">{{ $productLabel }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="subscription-quantity">Quantity</label>
                    <input id="subscription-quantity" class="form-control" type="number" min="1" wire:model.blur="quantity">
                    @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="subscription-frequency">Frequency</label>
                    <select id="subscription-frequency" class="form-control" wire:model.live="formFrequency">
                        @foreach($frequencyLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('formFrequency') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                @if(in_array($formFrequency, ['weekly', 'custom_days'], true))
                    <div class="form-group form-group-wide">
                        <label class="form-label">Delivery Days</label>
                        <div class="choice-grid">
                            @foreach($deliveryDayOptions as $value => $label)
                                <label class="check-control">
                                    <input type="checkbox" value="{{ $value }}" wire:model="delivery_days">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('delivery_days') <p class="form-error">{{ $message }}</p> @enderror
                        @error('delivery_days.*') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="subscription-slot">Delivery Slot</label>
                    <select id="subscription-slot" class="form-control" wire:model.live="preferred_delivery_slot">
                        @foreach($slotLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('preferred_delivery_slot') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                @if($preferred_delivery_slot === 'custom')
                    <div class="form-group">
                        <label class="form-label" for="subscription-time">Delivery Time</label>
                        <input id="subscription-time" class="form-control" type="time" wire:model="preferred_delivery_time">
                        @error('preferred_delivery_time') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="subscription-start-date">Start Date</label>
                    <input id="subscription-start-date" class="form-control" type="date" wire:model="start_date">
                    @error('start_date') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="subscription-next-date">Next Delivery Date</label>
                    <input id="subscription-next-date" class="form-control" type="date" wire:model="next_delivery_date">
                    @error('next_delivery_date') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="subscription-status">Status</label>
                    <select id="subscription-status" class="form-control" wire:model="formStatus">
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('formStatus') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                @if($formStatus === 'paused')
                    <div class="form-group">
                        <label class="form-label" for="subscription-paused-from">Paused From</label>
                        <input id="subscription-paused-from" class="form-control" type="date" wire:model="paused_from">
                        @error('paused_from') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subscription-paused-to">Paused To</label>
                        <input id="subscription-paused-to" class="form-control" type="date" wire:model="paused_to">
                        @error('paused_to') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group form-group-wide">
                        <label class="form-label" for="subscription-pause-reason">Pause Reason</label>
                        <textarea id="subscription-pause-reason" class="form-control" rows="2" wire:model.blur="pause_reason"></textarea>
                        @error('pause_reason') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="form-group form-group-wide">
                    <label class="form-label" for="subscription-remarks">Remarks</label>
                    <textarea id="subscription-remarks" class="form-control" rows="3" wire:model.blur="remarks"></textarea>
                    @error('remarks') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Subscription</button>
                    <button type="button" class="btn btn-muted" wire:click="cancel">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    @if($isPauseFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Pause Subscription</h2>
                    <p class="panel-subtitle">Set the pause window and optional reason.</p>
                </div>
            </div>

            <form wire:submit="pause" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="pause-from">Paused From</label>
                    <input id="pause-from" class="form-control" type="date" wire:model="pause_from">
                    @error('pause_from') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="pause-to">Paused To</label>
                    <input id="pause-to" class="form-control" type="date" wire:model="pause_to">
                    @error('pause_to') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group form-group-wide">
                    <label class="form-label" for="pause-reason">Reason</label>
                    <textarea id="pause-reason" class="form-control" rows="2" wire:model.blur="pauseReason"></textarea>
                    @error('pauseReason') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Pause</button>
                    <button type="button" class="btn btn-muted" wire:click="cancelPause">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    <section class="panel">
        <div class="toolbar">
            <input type="search" class="form-control search-input" placeholder="Search subscription, customer, mobile, or product" wire:model.live.debounce.300ms="search">

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

            <select class="form-control filter-control" wire:model.live="frequency">
                <option value="">All frequencies</option>
                @foreach($frequencyLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select class="form-control filter-control" wire:model.live="status">
                <option value="">All statuses</option>
                @foreach($statusLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <label class="check-control">
                <input type="checkbox" wire:model.live="dueForDelivery">
                <span>Due today</span>
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
                        <th>Subscription</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Frequency</th>
                        <th>Slot</th>
                        <th>Next Delivery</th>
                        <th>Status</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr @class(['is-muted' => $subscription->trashed()])>
                            <td>
                                <span class="mono">{{ $subscription->subscription_no }}</span>
                                @if($subscription->trashed())
                                    <span class="badge badge-muted">Trashed</span>
                                @endif
                            </td>
                            <td>
                                <div><span class="mono">{{ $subscription->customer?->customer_id ?? '-' }}</span></div>
                                <strong>{{ $subscription->customer?->name ?? 'Deleted customer' }}</strong>
                                <div class="text-muted">{{ $subscription->customer?->mobile }}</div>
                            </td>
                            <td>{{ $subscription->product?->name ?? 'Deleted product' }}</td>
                            <td>{{ $subscription->quantity }}</td>
                            <td><span class="badge badge-muted">{{ $frequencyLabels[$subscription->frequency] ?? ucfirst($subscription->frequency) }}</span></td>
                            <td>
                                <span class="badge badge-muted">{{ $slotLabels[$subscription->preferred_delivery_slot] ?? ucfirst($subscription->preferred_delivery_slot) }}</span>
                                @if($subscription->preferred_delivery_time)
                                    <div class="text-muted">{{ substr((string) $subscription->preferred_delivery_time, 0, 5) }}</div>
                                @endif
                            </td>
                            <td>{{ $subscription->next_delivery_date?->format('Y-m-d') ?? '-' }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-success' => $subscription->status === 'active',
                                    'badge-warning' => $subscription->status === 'paused',
                                    'badge-danger' => $subscription->status === 'cancelled',
                                    'badge-muted' => $subscription->status === 'inactive',
                                ])>
                                    {{ $statusLabels[$subscription->status] ?? ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td>
                                @if($canManage)
                                    <div class="row-actions">
                                        @if($subscription->trashed())
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="restore({{ $subscription->id }})">Restore</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="forceDelete({{ $subscription->id }})" wire:confirm="Permanently delete this subscription?">Delete Forever</button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="edit({{ $subscription->id }})">Edit</button>
                                            @if($subscription->isActive())
                                                <button type="button" class="btn btn-sm btn-muted" wire:click="openPauseForm({{ $subscription->id }})">Pause</button>
                                                <button type="button" class="btn btn-sm btn-muted" wire:click="recalculateNextDate({{ $subscription->id }})">Recalc</button>
                                            @endif
                                            @if($subscription->isPaused())
                                                <button type="button" class="btn btn-sm btn-success" wire:click="resume({{ $subscription->id }})" wire:confirm="Resume this subscription?">Resume</button>
                                            @endif
                                            @if(in_array($subscription->status, ['active', 'paused'], true))
                                                <button type="button" class="btn btn-sm btn-danger" wire:click="cancelSubscription({{ $subscription->id }})" wire:confirm="Cancel this subscription?">Cancel</button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="delete({{ $subscription->id }})" wire:confirm="Move this subscription to trash?">Delete</button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9"><div class="empty-state">No subscriptions found.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $subscriptions->links() }}
        </div>
    </section>
</div>
