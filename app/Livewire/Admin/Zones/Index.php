<?php

namespace App\Livewire\Admin\Zones;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $manager = '';

    public bool $showTrashed = false;

    public bool $isFormOpen = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $code = '';

    public ?string $description = null;

    public string|int|null $delivery_manager_id = null;

    public string $formStatus = 'active';

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('zones.view'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingManager(): void
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

    public function edit(int $zoneId): void
    {
        $this->authorizeManage();

        $zone = Zone::withTrashed()->findOrFail($zoneId);

        $this->editingId = $zone->id;
        $this->name = $zone->name;
        $this->code = $zone->code;
        $this->description = $zone->description;
        $this->delivery_manager_id = $zone->delivery_manager_id;
        $this->formStatus = $zone->status;
        $this->isFormOpen = true;
    }

    public function save(): void
    {
        $this->authorizeManage();

        if ($this->delivery_manager_id === '') {
            $this->delivery_manager_id = null;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('zones', 'code')->ignore($this->editingId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'delivery_manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'formStatus' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        Zone::withTrashed()->updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'],
                'delivery_manager_id' => $validated['delivery_manager_id'],
                'status' => $validated['formStatus'],
            ],
        );

        session()->flash('success', $this->editingId ? 'Zone updated successfully.' : 'Zone created successfully.');

        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function delete(int $zoneId): void
    {
        $this->authorizeManage();

        Zone::findOrFail($zoneId)->delete();
        session()->flash('success', 'Zone moved to trash.');
    }

    public function restore(int $zoneId): void
    {
        $this->authorizeManage();

        Zone::onlyTrashed()->findOrFail($zoneId)->restore();
        session()->flash('success', 'Zone restored successfully.');
    }

    public function forceDelete(int $zoneId): void
    {
        $this->authorizeManage();

        Zone::onlyTrashed()->findOrFail($zoneId)->forceDelete();
        session()->flash('success', 'Zone permanently deleted.');
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function render(): View
    {
        $zones = Zone::query()
            ->with('deliveryManager')
            ->withCount('customers')
            ->when($this->showTrashed, fn ($query) => $query->withTrashed())
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%')
                        ->orWhereHas('deliveryManager', fn ($managerQuery) => $managerQuery->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->manager !== '', fn ($query) => $query->where('delivery_manager_id', $this->manager))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.zones.index', [
            'zones' => $zones,
            'managers' => User::orderBy('name')->pluck('name', 'id'),
            'canManage' => auth()->user()?->can('zones.manage') ?? false,
        ])->layout('admin.layouts.app', ['title' => 'Zones / Lines']);
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('zones.manage'), 403);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->name = '';
        $this->code = '';
        $this->description = null;
        $this->delivery_manager_id = null;
        $this->formStatus = 'active';
    }
}
