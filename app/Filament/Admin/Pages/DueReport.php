<?php

namespace App\Filament\Admin\Pages;

use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Zone;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class DueReport extends Page implements HasForms
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'reports.due.view';
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Due Report';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.due-report';

    public string  $party_type       = 'all';
    public ?int    $zone_id          = null;
    public ?string $approval_status  = null;
    public bool    $include_zero_due = false;

    public function getRows(): Collection
    {
        $rows = collect();

        if (in_array($this->party_type, ['all', 'customer'])) {
            $customers = Customer::with('zone')
                ->when(! $this->include_zero_due, fn ($q) => $q->where('current_due', '>', 0))
                ->when($this->zone_id,         fn ($q) => $q->where('zone_id', $this->zone_id))
                ->when($this->approval_status, fn ($q) => $q->where('approval_status', $this->approval_status))
                ->orderByDesc('current_due')
                ->get()
                ->map(fn (Customer $c) => [
                    'party_type'      => 'Customer',
                    'code'            => $c->customer_id,
                    'name'            => $c->name,
                    'mobile'          => $c->mobile,
                    'zone'            => $c->zone?->name ?? '—',
                    'current_due'     => (float) $c->current_due,
                    'approval_status' => $c->approval_status,
                ]);
            $rows = $rows->merge($customers);
        }

        if (in_array($this->party_type, ['all', 'dealer'])) {
            $dealers = Dealer::with('zone')
                ->when(! $this->include_zero_due, fn ($q) => $q->where('current_due', '>', 0))
                ->when($this->zone_id,         fn ($q) => $q->where('zone_id', $this->zone_id))
                ->when($this->approval_status, fn ($q) => $q->where('approval_status', $this->approval_status))
                ->orderByDesc('current_due')
                ->get()
                ->map(fn (Dealer $d) => [
                    'party_type'      => 'Dealer',
                    'code'            => $d->dealer_code,
                    'name'            => $d->name,
                    'mobile'          => $d->mobile,
                    'zone'            => $d->zone?->name ?? '—',
                    'current_due'     => (float) $d->current_due,
                    'approval_status' => $d->approval_status,
                ]);
            $rows = $rows->merge($dealers);
        }

        return $rows->sortByDesc('current_due')->values();
    }
}
