<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">Customers</h1>
            <p class="page-subtitle">Manage customer profiles, approval status, zones, QR actions, and login passwords.</p>
        </div>

        @if($permissions['create'])
            <button type="button" class="btn btn-primary" wire:click="create">New Customer</button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="metric-grid">
        <div class="metric-card">
            <span>Total Customers</span>
            <strong>{{ number_format($stats['total']) }}</strong>
        </div>
        <div class="metric-card">
            <span>Approved</span>
            <strong>{{ number_format($stats['approved']) }}</strong>
        </div>
        <div class="metric-card">
            <span>Pending Approval</span>
            <strong>{{ number_format($stats['pending']) }}</strong>
        </div>
        <div class="metric-card">
            <span>Current Due</span>
            <strong>BDT {{ number_format((float) $stats['due'], 2) }}</strong>
        </div>
    </div>

    @if($isFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">{{ $editingId ? 'Edit Customer' : 'Create Customer' }}</h2>
                    <p class="panel-subtitle">Customer ID is generated automatically for new records.</p>
                </div>
            </div>

            <form wire:submit="save" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="customer-name">Name</label>
                    <input id="customer-name" class="form-control" type="text" wire:model.blur="name">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-name-bn">Name (Bangla)</label>
                    <input id="customer-name-bn" class="form-control" type="text" wire:model.blur="name_bn">
                    @error('name_bn') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-mobile">Mobile</label>
                    <input id="customer-mobile" class="form-control" type="tel" wire:model.blur="mobile" placeholder="01XXXXXXXXX">
                    @error('mobile') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-email">Email</label>
                    <input id="customer-email" class="form-control" type="email" wire:model.blur="email">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-type">Type</label>
                    <select id="customer-type" class="form-control" wire:model="customer_type">
                        @foreach($typeLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('customer_type') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-zone">Zone / Line</label>
                    <select id="customer-zone" class="form-control" wire:model="zone_id">
                        <option value="">None</option>
                        @foreach($activeZones as $id => $zoneName)
                            <option value="{{ $id }}">{{ $zoneName }}</option>
                        @endforeach
                    </select>
                    @error('zone_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group form-group-wide">
                    <label class="form-label" for="customer-address">Address</label>
                    <textarea id="customer-address" class="form-control" rows="3" wire:model.blur="address"></textarea>
                    @error('address') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group form-group-wide">
                    <label class="form-label" for="customer-address-bn">Address (Bangla)</label>
                    <textarea id="customer-address-bn" class="form-control" rows="3" wire:model.blur="address_bn"></textarea>
                    @error('address_bn') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-approval-status">Approval Status</label>
                    <select id="customer-approval-status" class="form-control" wire:model="approval_status">
                        @foreach($approvalStatusLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('approval_status') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-delivery-slot">Default Delivery Slot</label>
                    <select id="customer-delivery-slot" class="form-control" wire:model="default_delivery_slot">
                        <option value="">None</option>
                        @foreach($deliverySlotLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('default_delivery_slot') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-opening-balance">Opening Balance</label>
                    <input id="customer-opening-balance" class="form-control" type="number" step="0.01" min="0" wire:model.blur="opening_balance">
                    @error('opening_balance') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-current-due">Current Due</label>
                    <input id="customer-current-due" class="form-control" type="number" step="0.01" wire:model.blur="current_due">
                    @error('current_due') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-jar-deposit">Jar Deposit Qty</label>
                    <input id="customer-jar-deposit" class="form-control" type="number" min="0" wire:model.blur="jar_deposit_qty">
                    @error('jar_deposit_qty') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-approved-by">Approved By</label>
                    <select id="customer-approved-by" class="form-control" wire:model="approved_by">
                        <option value="">None</option>
                        @foreach($users as $id => $userName)
                            <option value="{{ $id }}">{{ $userName }}</option>
                        @endforeach
                    </select>
                    @error('approved_by') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-approved-at">Approved At</label>
                    <input id="customer-approved-at" class="form-control" type="datetime-local" wire:model="approved_at">
                    @error('approved_at') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-user">Linked User Account</label>
                    <select id="customer-user" class="form-control" wire:model="user_id">
                        <option value="">None</option>
                        @foreach($users as $id => $userName)
                            <option value="{{ $id }}">{{ $userName }}</option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-qr">QR Code</label>
                    <input id="customer-qr" class="form-control" type="text" wire:model.blur="qr_code">
                    @error('qr_code') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                    <button type="button" class="btn btn-muted" wire:click="cancel">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    @if($isPasswordFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Change Customer Password</h2>
                    <p class="panel-subtitle">Login email: {{ $loginEmail }}</p>
                </div>
            </div>

            <form wire:submit="savePassword" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="customer-password">New Password</label>
                    <input id="customer-password" class="form-control" type="password" wire:model="password">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="customer-password-confirmation">Confirm Password</label>
                    <input id="customer-password-confirmation" class="form-control" type="password" wire:model="password_confirmation">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                    <button type="button" class="btn btn-muted" wire:click="cancelPassword">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    <section class="panel">
        <div class="toolbar">
            <input type="search" class="form-control search-input" placeholder="Search ID, name, mobile, or email" wire:model.live.debounce.300ms="search">

            <select class="form-control filter-control" wire:model.live="zone">
                <option value="">All zones</option>
                @foreach($zones as $id => $zoneName)
                    <option value="{{ $id }}">{{ $zoneName }}</option>
                @endforeach
            </select>

            <select class="form-control filter-control" wire:model.live="customerType">
                <option value="">All types</option>
                @foreach($typeLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select class="form-control filter-control" wire:model.live="approvalStatus">
                <option value="">All statuses</option>
                @foreach($approvalStatusLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

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
                        <th>Zone</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th>Jar Dep.</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr @class(['is-muted' => $customer->trashed()])>
                            <td>
                                <div><span class="mono">{{ $customer->customer_id }}</span></div>
                                <strong>{{ $customer->name }}</strong>
                                @if($customer->name_bn)
                                    <div class="text-muted">{{ $customer->name_bn }}</div>
                                @endif
                                @if($customer->trashed())
                                    <span class="badge badge-muted">Trashed</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $customer->mobile }}</div>
                                @if($customer->email)
                                    <div class="text-muted">{{ $customer->email }}</div>
                                @endif
                            </td>
                            <td>{{ $customer->zone?->name ?? '-' }}</td>
                            <td><span class="badge badge-muted">{{ $typeLabels[$customer->customer_type] ?? ucfirst($customer->customer_type) }}</span></td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-success' => $customer->approval_status === 'approved',
                                    'badge-warning' => $customer->approval_status === 'pending',
                                    'badge-danger' => $customer->approval_status === 'rejected',
                                    'badge-muted' => $customer->approval_status === 'inactive',
                                ])>
                                    {{ $approvalStatusLabels[$customer->approval_status] ?? ucfirst($customer->approval_status) }}
                                </span>
                            </td>
                            <td>BDT {{ number_format((float) $customer->current_due, 2) }}</td>
                            <td>{{ $customer->jar_deposit_qty }}</td>
                            <td>
                                <div class="row-actions">
                                    @if($permissions['view'] && ! $customer->trashed())
                                        <a class="btn btn-sm btn-muted" href="{{ route('admin.customers.qr.show', $customer) }}" target="_blank">QR</a>
                                        <a class="btn btn-sm btn-muted" href="{{ route('admin.customers.qr.print', $customer) }}" target="_blank">Print</a>
                                    @endif

                                    @if($permissions['update'] && ! $customer->trashed())
                                        <button type="button" class="btn btn-sm btn-muted" wire:click="edit({{ $customer->id }})">Edit</button>
                                        <button type="button" class="btn btn-sm btn-muted" wire:click="openPasswordForm({{ $customer->id }})">Password</button>
                                        @if($customer->approval_status === 'approved')
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="markInactive({{ $customer->id }})" wire:confirm="Mark this customer inactive?">Inactive</button>
                                        @endif
                                    @endif

                                    @if($permissions['approve'] && ! $customer->trashed() && $customer->approval_status !== 'approved')
                                        <button type="button" class="btn btn-sm btn-success" wire:click="approve({{ $customer->id }})">Approve</button>
                                    @endif

                                    @if($permissions['reject'] && ! $customer->trashed() && $customer->approval_status === 'pending')
                                        <button type="button" class="btn btn-sm btn-danger" wire:click="reject({{ $customer->id }})" wire:confirm="Reject this customer?">Reject</button>
                                    @endif

                                    @if($permissions['delete'])
                                        @if($customer->trashed())
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="restore({{ $customer->id }})">Restore</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="forceDelete({{ $customer->id }})" wire:confirm="Permanently delete this customer?">Delete Forever</button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="delete({{ $customer->id }})" wire:confirm="Move this customer to trash?">Delete</button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"><div class="empty-state">No customers found.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $customers->links() }}
        </div>
    </section>
</div>
