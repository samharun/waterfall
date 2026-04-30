<?php

namespace App\Filament\Admin\Pages;

use App\Models\Customer;
use App\Models\Zone;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CustomerQrPrinting extends Page
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'customers.view';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static string|\UnitEnum|null $navigationGroup = 'Customers';

    protected static ?string $navigationLabel = 'QR Code Printing';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.admin.pages.customer-qr-printing';

    // ── Filter state ───────────────────────────────────────────────
    public ?int    $zone_id          = null;
    public ?string $approval_status  = 'approved';
    public ?string $search           = null;
    public array   $selected_ids     = [];
    public bool    $select_all       = false;

    public function getZones(): Collection
    {
        return Zone::orderBy('name')->get(['id', 'name']);
    }

    public function getCustomers(): Collection
    {
        return Customer::with('zone')
            ->when($this->zone_id,         fn ($q) => $q->where('zone_id', $this->zone_id))
            ->when($this->approval_status, fn ($q) => $q->where('approval_status', $this->approval_status))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_id', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%');
            }))
            ->orderBy('customer_id')
            ->limit(200)
            ->get();
    }

    public function getTotalCount(): int
    {
        return Customer::when($this->zone_id,         fn ($q) => $q->where('zone_id', $this->zone_id))
            ->when($this->approval_status, fn ($q) => $q->where('approval_status', $this->approval_status))
            ->count();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected_ids = $this->getCustomers()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected_ids = [];
        }
    }

    public function toggleSelect(string $id): void
    {
        if (in_array($id, $this->selected_ids)) {
            $this->selected_ids = array_values(array_filter($this->selected_ids, fn ($i) => $i !== $id));
        } else {
            $this->selected_ids[] = $id;
        }
        $this->select_all = false;
    }

    public function getBulkPrintUrl(): string
    {
        if (! empty($this->selected_ids)) {
            return route('admin.customers.qr.bulk-print', ['customer_ids' => $this->selected_ids]);
        }

        // No selection — use current filters
        $params = array_filter([
            'zone_id'         => $this->zone_id,
            'approval_status' => $this->approval_status,
        ]);

        return route('admin.customers.qr.bulk-print', $params);
    }

    public function getSelectedCount(): int
    {
        return count($this->selected_ids);
    }
}

