<x-filament-panels::page>
    @php
        $customers    = $this->getCustomers();
        $zones        = $this->getZones();
        $totalCount   = $this->getTotalCount();
        $selectedCount = $this->getSelectedCount();
        $approvedCount = \App\Models\Customer::where('approval_status', 'approved')->count();
        $pendingCount = \App\Models\Customer::where('approval_status', 'pending')->count();
        $zonedCount = \App\Models\Customer::whereNotNull('zone_id')->count();
    @endphp

    {{-- Page header actions --}}
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px">
        <div>
            <p style="font-size:13px;color:#6b7280;margin:0">
                Showing <strong>{{ $customers->count() }}</strong> of <strong>{{ $totalCount }}</strong> customers.
                @if($selectedCount > 0)
                    <span style="color:#2563eb;font-weight:600">{{ $selectedCount }} selected.</span>
                @endif
            </p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @if($selectedCount > 0)
                <a href="{{ $this->getBulkPrintUrl() }}"
                   target="_blank"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:#0077B6;color:#fff;font-size:13px;font-weight:600;text-decoration:none;border:none;cursor:pointer">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>
                    </svg>
                    Print {{ $selectedCount }} Selected QR Cards
                </a>
            @else
                <a href="{{ $this->getBulkPrintUrl() }}"
                   target="_blank"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:#0077B6;color:#fff;font-size:13px;font-weight:600;text-decoration:none">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>
                    </svg>
                    Print All Filtered ({{ $customers->count() }})
                </a>
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
        <div style="background:linear-gradient(135deg,#0077B6,#005f92);border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,119,182,.2)">
            <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Filtered Customers</div>
            <div style="font-size:30px;font-weight:800;color:#fff">{{ number_format($customers->count()) }}</div>
        </div>
        <div style="background:linear-gradient(135deg,#16a34a,#15803d);border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(22,163,74,.2)">
            <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Approved</div>
            <div style="font-size:30px;font-weight:800;color:#fff">{{ number_format($approvedCount) }}</div>
        </div>
        <div style="background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(245,158,11,.2)">
            <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Pending Approval</div>
            <div style="font-size:30px;font-weight:800;color:#fff">{{ number_format($pendingCount) }}</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
            <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Assigned Zones</div>
            <div style="font-size:30px;font-weight:800;color:#0077B6">{{ number_format($zonedCount) }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;box-shadow:0 1px 3px rgba(0,0,0,.06)">

        {{-- Search --}}
        <div style="flex:1;min-width:180px">
            <label style="display:block;font-size:11px;font-weight:600;color:#374151;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Search</label>
            <input
                wire:model.live.debounce.400ms="search"
                type="text"
                placeholder="Name, ID or mobile..."
                style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box"
            />
        </div>

        {{-- Zone filter --}}
        <div style="min-width:160px">
            <label style="display:block;font-size:11px;font-weight:600;color:#374151;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Zone</label>
            <select
                wire:model.live="zone_id"
                style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
                <option value="">All Zones</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Status filter --}}
        <div style="min-width:140px">
            <label style="display:block;font-size:11px;font-weight:600;color:#374151;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Status</label>
            <select
                wire:model.live="approval_status"
                style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
                <option value="">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        {{-- Clear --}}
        <button
            wire:click="$set('search', null); $set('zone_id', null); $set('approval_status', 'approved'); $set('selected_ids', []); $set('select_all', false)"
            style="padding:8px 14px;border:1px solid #d1d5db;border-radius:8px;background:#f9fafb;color:#374151;font-size:12px;font-weight:600;cursor:pointer">
            Clear
        </button>
    </div>

    {{-- Customer table --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)">

        {{-- Table header --}}
        <div style="display:grid;grid-template-columns:36px 1fr 120px 140px 100px 160px;gap:0;background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:10px 16px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">
            <div>
                <input type="checkbox"
                       wire:model.live="select_all"
                       style="width:14px;height:14px;cursor:pointer">
            </div>
            <div>Customer</div>
            <div>ID</div>
            <div>Zone</div>
            <div>Status</div>
            <div style="text-align:right">Actions</div>
        </div>

        @forelse($customers as $customer)
            @php
                $isSelected = in_array((string) $customer->id, $this->selected_ids);
                $statusColors = [
                    'approved' => 'background:#f0fdf4;color:#15803d',
                    'pending'  => 'background:#fef9c3;color:#92400e',
                    'rejected' => 'background:#fef2f2;color:#b91c1c',
                    'inactive' => 'background:#f3f4f6;color:#6b7280',
                ];
                $statusStyle = $statusColors[$customer->approval_status] ?? $statusColors['inactive'];
            @endphp
            <div style="display:grid;grid-template-columns:36px 1fr 120px 140px 100px 160px;gap:0;padding:10px 16px;border-bottom:1px solid #f9fafb;align-items:center;background:{{ $isSelected ? '#eff6ff' : '#fff' }};transition:background .1s"
                 wire:key="customer-{{ $customer->id }}">

                {{-- Checkbox --}}
                <div>
                    <input type="checkbox"
                           wire:click="toggleSelect('{{ $customer->id }}')"
                           {{ $isSelected ? 'checked' : '' }}
                           style="width:14px;height:14px;cursor:pointer">
                </div>

                {{-- Name --}}
                <div>
                    <div style="font-size:13px;font-weight:600;color:#111827">{{ $customer->name }}</div>
                    <div style="font-size:11px;color:#9ca3af">{{ $customer->mobile }}</div>
                </div>

                {{-- Customer ID --}}
                <div style="font-size:11px;font-family:monospace;color:#0077B6;font-weight:600">
                    {{ $customer->customer_id }}
                </div>

                {{-- Zone --}}
                <div style="font-size:12px;color:#374151">
                    {{ $customer->zone?->name ?? '—' }}
                </div>

                {{-- Status --}}
                <div>
                    <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:9999px;{{ $statusStyle }}">
                        {{ ucfirst($customer->approval_status) }}
                    </span>
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:5px;justify-content:flex-end">
                    <a href="{{ route('admin.customers.qr.show', $customer) }}"
                       target="_blank"
                       title="View QR"
                       style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;text-decoration:none">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"/>
                        </svg>
                    </a>
                    <a href="{{ route('admin.customers.qr.print', $customer) }}"
                       target="_blank"
                       title="Print QR Card"
                       style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;text-decoration:none">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>
                        </svg>
                    </a>
                    <a href="{{ route('admin.customers.qr.download', $customer) }}"
                       title="Download QR SVG"
                       style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;background:#f9fafb;border:1px solid #e5e7eb;color:#374151;text-decoration:none">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div style="padding:40px;text-align:center;color:#9ca3af">
                <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
                <p style="font-size:13px">No customers found matching your filters.</p>
            </div>
        @endforelse
    </div>

    {{-- Selection summary bar --}}
    @if($selectedCount > 0)
        <div style="position:sticky;bottom:0;margin-top:12px;padding:12px 16px;background:#0077B6;border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:12px;box-shadow:0 2px 8px rgba(0,119,182,.2)">
            <span style="font-size:13px;color:#fff;font-weight:500">
                {{ $selectedCount }} customer{{ $selectedCount !== 1 ? 's' : '' }} selected
            </span>
            <div style="display:flex;gap:8px">
                <button
                    wire:click="$set('selected_ids', []); $set('select_all', false)"
                    style="padding:6px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.4);background:transparent;color:#fff;font-size:12px;cursor:pointer">
                    Clear Selection
                </button>
                <a href="{{ $this->getBulkPrintUrl() }}"
                   target="_blank"
                   style="padding:6px 14px;border-radius:6px;background:#fff;color:#0077B6;font-size:12px;font-weight:700;text-decoration:none">
                    Print {{ $selectedCount }} QR Cards →
                </a>
            </div>
        </div>
    @endif

</x-filament-panels::page>
