<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">Delivery Assignments</h1>
            <p class="page-subtitle">Assign staff, track delivery status, and keep order status synchronized.</p>
        </div>

        @if($permissions['create'])
            <button type="button" class="btn btn-primary" wire:click="create">New Delivery</button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($isFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">{{ $editingId ? 'Edit Delivery' : 'Create Delivery' }}</h2>
                    <p class="panel-subtitle">Order status and due recalculation remain handled by the Delivery model.</p>
                </div>
            </div>

            <form wire:submit="save" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="delivery-order">Order</label>
                    <select id="delivery-order" class="form-control" wire:model.live="order_id">
                        <option value="">Select order</option>
                        @foreach($orders as $id => $orderLabel)
                            <option value="{{ $id }}">{{ $orderLabel }}</option>
                        @endforeach
                    </select>
                    @error('order_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="delivery-zone">Zone</label>
                    <select id="delivery-zone" class="form-control" wire:model="zone_id">
                        <option value="">None</option>
                        @foreach($activeZones as $id => $zoneName)
                            <option value="{{ $id }}">{{ $zoneName }}</option>
                        @endforeach
                    </select>
                    @error('zone_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="delivery-staff">Delivery Staff</label>
                    <select id="delivery-staff" class="form-control" wire:model.live="delivery_staff_id">
                        <option value="">Unassigned</option>
                        @foreach($deliveryStaff as $id => $staffLabel)
                            <option value="{{ $id }}">{{ $staffLabel }}</option>
                        @endforeach
                    </select>
                    @error('delivery_staff_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="delivery-status">Status</label>
                    <select id="delivery-status" class="form-control" wire:model.live="delivery_status">
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('delivery_status') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="delivery-assigned-by">Assigned By</label>
                    <select id="delivery-assigned-by" class="form-control" wire:model="assigned_by">
                        <option value="">None</option>
                        @foreach($backOfficeUsers as $id => $userName)
                            <option value="{{ $id }}">{{ $userName }}</option>
                        @endforeach
                    </select>
                    @error('assigned_by') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="delivery-assigned-at">Assigned At</label>
                    <input id="delivery-assigned-at" class="form-control" type="datetime-local" wire:model="assigned_at">
                    @error('assigned_at') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="delivery-delivered-at">Delivered At</label>
                    <input id="delivery-delivered-at" class="form-control" type="datetime-local" wire:model="delivered_at">
                    @error('delivered_at') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group form-group-wide">
                    <label class="form-label" for="delivery-note">Delivery Note</label>
                    <textarea id="delivery-note" class="form-control" rows="3" wire:model.blur="delivery_note"></textarea>
                    @error('delivery_note') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                @if($delivery_status === 'failed')
                    <div class="form-group form-group-wide">
                        <label class="form-label" for="failure-reason">Failure Reason</label>
                        <textarea id="failure-reason" class="form-control" rows="3" wire:model.blur="failure_reason"></textarea>
                        @error('failure_reason') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Delivery</button>
                    <button type="button" class="btn btn-muted" wire:click="cancel">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    @if($isAssignFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Assign Staff</h2>
                    <p class="panel-subtitle">Assigning staff marks a pending delivery as assigned.</p>
                </div>
            </div>

            <form wire:submit="assignStaff" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="assign-staff">Delivery Staff</label>
                    <select id="assign-staff" class="form-control" wire:model="assign_staff_id">
                        <option value="">Select staff</option>
                        @foreach($deliveryStaff as $id => $staffLabel)
                            <option value="{{ $id }}">{{ $staffLabel }}</option>
                        @endforeach
                    </select>
                    @error('assign_staff_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Assign</button>
                    <button type="button" class="btn btn-muted" wire:click="cancelAssign">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    @if($isFailFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Mark Delivery Failed</h2>
                    <p class="panel-subtitle">A failure reason is required.</p>
                </div>
            </div>

            <form wire:submit="markFailed" class="form-grid">
                <div class="form-group form-group-wide">
                    <label class="form-label" for="fail-reason">Failure Reason</label>
                    <textarea id="fail-reason" class="form-control" rows="3" wire:model.blur="failReason"></textarea>
                    @error('failReason') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-danger">Mark Failed</button>
                    <button type="button" class="btn btn-muted" wire:click="cancelFail">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    <section class="panel">
        <div class="toolbar">
            <input type="search" class="form-control search-input" placeholder="Search delivery, order, customer, dealer, or mobile" wire:model.live.debounce.300ms="search">

            <select class="form-control filter-control" wire:model.live="zone">
                <option value="">All zones</option>
                @foreach($zones as $id => $zoneName)
                    <option value="{{ $id }}">{{ $zoneName }}</option>
                @endforeach
            </select>

            <select class="form-control filter-control" wire:model.live="staff">
                <option value="">All staff</option>
                @foreach($deliveryStaff as $id => $staffLabel)
                    <option value="{{ $id }}">{{ $staffLabel }}</option>
                @endforeach
            </select>

            <select class="form-control filter-control" wire:model.live="status">
                <option value="">All statuses</option>
                @foreach($statusLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <label class="check-control">
                <input type="checkbox" wire:model.live="showTrashed">
                <span>Show trashed</span>
            </label>
        </div>

        <div class="toolbar">
            <input class="form-control filter-control" type="date" wire:model.live="assignedFrom" aria-label="Assigned from">
            <input class="form-control filter-control" type="date" wire:model.live="assignedUntil" aria-label="Assigned until">
            <input class="form-control filter-control" type="date" wire:model.live="deliveredFrom" aria-label="Delivered from">
            <input class="form-control filter-control" type="date" wire:model.live="deliveredUntil" aria-label="Delivered until">
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Delivery</th>
                        <th>Order</th>
                        <th>Customer / Dealer</th>
                        <th>Zone</th>
                        <th>Staff</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th>Delivered</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                        @php
                            $order = $delivery->order;
                            $party = '-';
                            if ($order?->order_type === 'customer' && $order->customer) {
                                $party = $order->customer->customer_id . ' - ' . $order->customer->name;
                            } elseif ($order?->order_type === 'dealer' && $order->dealer) {
                                $party = $order->dealer->dealer_code . ' - ' . $order->dealer->name;
                            }
                        @endphp
                        <tr @class(['is-muted' => $delivery->trashed()])>
                            <td>
                                <span class="mono">{{ $delivery->delivery_no }}</span>
                                @if($delivery->trashed())
                                    <span class="badge badge-muted">Trashed</span>
                                @endif
                            </td>
                            <td><span class="mono">{{ $order?->order_no ?? '-' }}</span></td>
                            <td>{{ $party }}</td>
                            <td>{{ $delivery->zone?->name ?? '-' }}</td>
                            <td>{{ $delivery->deliveryStaff?->name ?? 'Unassigned' }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-success' => $delivery->delivery_status === 'delivered',
                                    'badge-warning' => in_array($delivery->delivery_status, ['assigned', 'in_progress', 'partial_delivered'], true),
                                    'badge-danger' => in_array($delivery->delivery_status, ['not_delivered', 'customer_unavailable', 'failed'], true),
                                    'badge-muted' => in_array($delivery->delivery_status, ['pending', 'cancelled'], true),
                                ])>
                                    {{ $statusLabels[$delivery->delivery_status] ?? ucfirst($delivery->delivery_status) }}
                                </span>
                            </td>
                            <td>{{ $delivery->assigned_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $delivery->delivered_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>
                                <div class="row-actions">
                                    @if($permissions['update'] && ! $delivery->trashed())
                                        <button type="button" class="btn btn-sm btn-muted" wire:click="edit({{ $delivery->id }})">Edit</button>
                                        @if($delivery->delivery_status === 'pending')
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="openAssignForm({{ $delivery->id }})">Assign</button>
                                        @endif
                                        @if($delivery->delivery_status === 'assigned')
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="markInProgress({{ $delivery->id }})" wire:confirm="Mark delivery in progress?">Progress</button>
                                        @endif
                                        @if(in_array($delivery->delivery_status, ['assigned', 'in_progress'], true))
                                            <button type="button" class="btn btn-sm btn-success" wire:click="markDelivered({{ $delivery->id }})" wire:confirm="Mark this delivery completed?">Delivered</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="openFailForm({{ $delivery->id }})">Failed</button>
                                        @endif
                                        @if(in_array($delivery->delivery_status, ['pending', 'assigned', 'in_progress'], true))
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="markCancelled({{ $delivery->id }})" wire:confirm="Cancel this delivery?">Cancel</button>
                                        @endif
                                    @endif

                                    @if($permissions['delete'])
                                        @if($delivery->trashed())
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="restore({{ $delivery->id }})">Restore</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="forceDelete({{ $delivery->id }})" wire:confirm="Permanently delete this delivery?">Delete Forever</button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="delete({{ $delivery->id }})" wire:confirm="Move this delivery to trash?">Delete</button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9"><div class="empty-state">No deliveries found.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $deliveries->links() }}
        </div>
    </section>
</div>
