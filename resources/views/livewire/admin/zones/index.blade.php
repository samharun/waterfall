<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">Zones / Lines</h1>
            <p class="page-subtitle">Manage delivery zones, line codes, status, and delivery managers.</p>
        </div>

        @if($canManage)
            <button type="button" class="btn btn-primary" wire:click="create">
                New Zone
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($isFormOpen)
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">{{ $editingId ? 'Edit Zone' : 'Create Zone' }}</h2>
                    <p class="panel-subtitle">Fields match the current Filament Zone resource.</p>
                </div>
            </div>

            <form wire:submit="save" class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="zone-name">Name</label>
                    <input id="zone-name" type="text" class="form-control" wire:model.blur="name">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="zone-code">Code</label>
                    <input id="zone-code" type="text" class="form-control" wire:model.blur="code" placeholder="MDP-L1">
                    @error('code') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="zone-manager">Delivery Manager</label>
                    <select id="zone-manager" class="form-control" wire:model="delivery_manager_id">
                        <option value="">None</option>
                        @foreach($managers as $id => $managerName)
                            <option value="{{ $id }}">{{ $managerName }}</option>
                        @endforeach
                    </select>
                    @error('delivery_manager_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="zone-status">Status</label>
                    <select id="zone-status" class="form-control" wire:model="formStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    @error('formStatus') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group form-group-wide">
                    <label class="form-label" for="zone-description">Description</label>
                    <textarea id="zone-description" class="form-control" rows="3" wire:model.blur="description"></textarea>
                    @error('description') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Zone</button>
                    <button type="button" class="btn btn-muted" wire:click="cancel">Cancel</button>
                </div>
            </form>
        </section>
    @endif

    <section class="panel">
        <div class="toolbar">
            <input type="search" class="form-control search-input" placeholder="Search code, name, or manager" wire:model.live.debounce.300ms="search">

            <select class="form-control filter-control" wire:model.live="status">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <select class="form-control filter-control" wire:model.live="manager">
                <option value="">All managers</option>
                @foreach($managers as $id => $managerName)
                    <option value="{{ $id }}">{{ $managerName }}</option>
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
                        <th>Code</th>
                        <th>Name</th>
                        <th>Delivery Manager</th>
                        <th>Customers</th>
                        <th>Status</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($zones as $zone)
                        <tr @class(['is-muted' => $zone->trashed()])>
                            <td><span class="mono">{{ $zone->code }}</span></td>
                            <td>
                                <strong>{{ $zone->name }}</strong>
                                @if($zone->trashed())
                                    <span class="badge badge-muted">Trashed</span>
                                @endif
                            </td>
                            <td>{{ $zone->deliveryManager?->name ?? '-' }}</td>
                            <td>{{ $zone->customers_count }}</td>
                            <td>
                                <span @class(['badge', 'badge-success' => $zone->status === 'active', 'badge-muted' => $zone->status !== 'active'])>
                                    {{ ucfirst($zone->status) }}
                                </span>
                            </td>
                            <td>
                                @if($canManage)
                                    <div class="row-actions">
                                        @if($zone->trashed())
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="restore({{ $zone->id }})">Restore</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="forceDelete({{ $zone->id }})" wire:confirm="Permanently delete this zone?">Delete Forever</button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-muted" wire:click="edit({{ $zone->id }})">Edit</button>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="delete({{ $zone->id }})" wire:confirm="Move this zone to trash?">Delete</button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">No zones found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $zones->links() }}
        </div>
    </section>
</div>
