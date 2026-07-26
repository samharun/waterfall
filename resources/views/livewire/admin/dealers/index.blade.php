<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">Dealers</h1>
            <p class="page-subtitle">Manage dealer accounts, approval status, zones, balances, and portal passwords.</p>
        </div>

        @if($permissions['create'])
            <button type="button" class="btn btn-primary" wire:click="create">New Dealer</button>
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
            <span>Total Dealers</span>
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
                    <h2 class="panel-title">{{ $editingId ? 'Edit Dealer' : 'Create Dealer' }}</h2>
                    <p class="panel-subtitle">Dealer code is generated automatically for new records.</p>
                </div>
            </div>

            <form wire:submit="save" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="dealer-name">Name</label>
                    <input id="dealer-name" class="form-control" type="text" wire:model.blur="name">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-mobile">Mobile</label>
                    <input id="dealer-mobile" class="form-control" type="tel" wire:model.blur="mobile" placeholder="01XXXXXXXXX">
                    @error('mobile') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-email">Email</label>
                    <input id="dealer-email" class="form-control" type="email" wire:model.blur="email">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-zone">Zone / Line</label>
                    <select id="dealer-zone" class="form-control" wire:model="zone_id">
                        <option value="">None</option>
                        @foreach($activeZones as $id => $zoneName)
                            <option value="{{ $id }}">{{ $zoneName }}</option>
                        @endforeach
                    </select>
                    @error('zone_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group form-group-wide">
                    <label class="form-label" for="dealer-address">Address</label>
                    <textarea id="dealer-address" class="form-control" rows="3" wire:model.blur="address"></textarea>
                    @error('address') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-status">Approval Status</label>
                    <select id="dealer-status" class="form-control" wire:model="approval_status">
                        @foreach($approvalStatusLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('approval_status') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-opening-balance">Opening Balance</label>
                    <input id="dealer-opening-balance" class="form-control" type="number" step="0.01" min="0" wire:model.blur="opening_balance">
                    @error('opening_balance') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-current-due">Current Due</label>
                    <input id="dealer-current-due" class="form-control" type="number" step="0.01" wire:model.blur="current_due">
                    @error('current_due') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-approved-by">Approved By</label>
                    <select id="dealer-approved-by" class="form-control" wire:model="approved_by">
                        <option value="">None</option>
                        @foreach($users as $id => $userName)
                            <option value="{{ $id }}">{{ $userName }}</option>
                        @endforeach
                    </select>
                    @error('approved_by') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-approved-at">Approved At</label>
                    <input id="dealer-approved-at" class="form-control" type="datetime-local" wire:model="approved_at">
                    @error('approved_at') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-user">Linked User Account</label>
                    <select id="dealer-user" class="form-control" wire:model="user_id">
                        <option value="">None</option>
                        @foreach($users as $id => $userName)
                            <option value="{{ $id }}">{{ $userName }}</option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Dealer</button>
                    <button type="button" class="btn btn-muted" wire:click="cancel">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    @if($isPasswordFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Change Dealer Password</h2>
                    <p class="panel-subtitle">Login email: {{ $loginEmail }}</p>
                </div>
            </div>

            <form wire:submit="savePassword" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="dealer-password">New Password</label>
                    <input id="dealer-password" class="form-control" type="password" wire:model="password">
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="dealer-password-confirmation">Confirm Password</label>
                    <input id="dealer-password-confirmation" class="form-control" type="password" wire:model="password_confirmation">
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
            <input type="search" class="form-control search-input" placeholder="Search code, name, mobile, or email" wire:model.live.debounce.300ms="search">

            <select class="form-control filter-control" wire:model.live="zone">
                <option value="">All zones</option>
                @foreach($zones as $id => $zoneName)
                    <option value="{{ $id }}">{{ $zoneName }}</option>
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
                        <th>Dealer</th>
                        <th>Mobile</th>
                        <th>Zone</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dealers as $dealer)
                        <tr @class(['is-muted' => $dealer->trashed()])>
                            <td>
                                <div><span class="mono">{{ $dealer->dealer_code }}</span></div>
                                <strong>{{ $dealer->name }}</strong>
                                @if($dealer->trashed())
                                    <span class="badge badge-muted">Trashed</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $dealer->mobile }}</div>
                                @if($dealer->email)
                                    <div class="text-muted">{{ $dealer->email }}</div>
                                @endif
                            </td>
                            <td>{{ $dealer->zone?->name ?? '-' }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-success' => $dealer->approval_status === 'approved',
                                    'badge-warning' => $dealer->approval_status === 'pending',
                                    'badge-danger' => $dealer->approval_status === 'rejected',
                                    'badge-muted' => $dealer->approval_status === 'inactive',
                                ])>
                                    {{ $approvalStatusLabels[$dealer->approval_status] ?? ucfirst($dealer->approval_status) }}
                                </span>
                            </td>
                            <td>BDT {{ number_format((float) $dealer->current_due, 2) }}</td>
                            <td>
                                <div class="row-actions">
                                    @if($permissions['update'] && ! $dealer->trashed())
                                        <button type="button" class="btn btn-sm btn-muted" wire:click="edit({{ $dealer->id }})">Edit</button>
                                        <button type="button" class="btn btn-sm btn-muted" wire:click="openPasswordForm({{ $dealer->id }})">Password</button>
                                        @if($dealer->approval_status !== 'approved')
                                            <button type="button" class="btn btn-sm btn-success" wire:click="approve({{ $dealer->id }})" wire:confirm="Approve this dealer?">Approve</button>
                                        @endif
                                        @if($dealer->approval_status === 'pending')
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="reject({{ $dealer->id }})" wire:confirm="Reject this dealer?">Reject</button>
                                        @endif
                                        @if($dealer->approval_status === 'approved')
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="markInactive({{ $dealer->id }})" wire:confirm="Mark this dealer inactive?">Inactive</button>
                                        @endif
                                    @endif

                                    @if($permissions['delete'])
                                        @if($dealer->trashed())
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="restore({{ $dealer->id }})">Restore</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="forceDelete({{ $dealer->id }})" wire:confirm="Permanently delete this dealer?">Delete Forever</button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="delete({{ $dealer->id }})" wire:confirm="Move this dealer to trash?">Delete</button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><div class="empty-state">No dealers found.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $dealers->links() }}
        </div>
    </section>
</div>
