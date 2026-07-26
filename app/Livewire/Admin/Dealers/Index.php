<?php

namespace App\Livewire\Admin\Dealers;

use App\Models\Dealer;
use App\Models\User;
use App\Models\Zone;
use App\Notifications\DealerApprovedNotification;
use App\Support\SafeNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $zone = '';
    public string $approvalStatus = '';
    public bool $showTrashed = false;

    public bool $isFormOpen = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $mobile = '';
    public ?string $email = null;
    public ?string $address = null;
    public string|int|null $zone_id = null;
    public string $approval_status = 'pending';
    public string|float|int $opening_balance = 0;
    public string|float|int $current_due = 0;
    public string|int|null $approved_by = null;
    public ?string $approved_at = null;
    public string|int|null $user_id = null;

    public bool $isPasswordFormOpen = false;
    public ?int $passwordDealerId = null;
    public string $loginEmail = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('dealers.view'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingZone(): void
    {
        $this->resetPage();
    }

    public function updatingApprovalStatus(): void
    {
        $this->resetPage();
    }

    public function updatingShowTrashed(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorizePermission('dealers.create');
        $this->resetForm();
        $this->isFormOpen = true;
    }

    public function edit(int $dealerId): void
    {
        $this->authorizePermission('dealers.update');

        $dealer = Dealer::withTrashed()->findOrFail($dealerId);

        $this->editingId = $dealer->id;
        $this->name = $dealer->name;
        $this->mobile = $dealer->mobile;
        $this->email = $dealer->email;
        $this->address = $dealer->address;
        $this->zone_id = $dealer->zone_id;
        $this->approval_status = $dealer->approval_status;
        $this->opening_balance = $dealer->opening_balance;
        $this->current_due = $dealer->current_due;
        $this->approved_by = $dealer->approved_by;
        $this->approved_at = $dealer->approved_at?->format('Y-m-d\TH:i');
        $this->user_id = $dealer->user_id;
        $this->isFormOpen = true;
    }

    public function save(): void
    {
        $this->authorizePermission($this->editingId ? 'dealers.update' : 'dealers.create');
        $this->normalizeNullableFields();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => [
                'required',
                'string',
                'max:11',
                'regex:/^01[3-9][0-9]{8}$/',
                Rule::unique('dealers', 'mobile')->ignore($this->editingId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'zone_id' => ['nullable', 'integer', Rule::exists('zones', 'id')],
            'approval_status' => ['required', Rule::in(array_keys(Dealer::approvalStatusLabels()))],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'current_due' => ['required', 'numeric'],
            'approved_by' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'approved_at' => ['nullable', 'date'],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        Dealer::withTrashed()->updateOrCreate(
            ['id' => $this->editingId],
            $validated,
        );

        session()->flash('success', $this->editingId ? 'Dealer updated successfully.' : 'Dealer created successfully.');

        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function approve(int $dealerId): void
    {
        $this->authorizePermission('dealers.update');

        $dealer = Dealer::findOrFail($dealerId);
        $dealer->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $emailNotificationFailed = false;

        if ($dealer->email && $dealer->user) {
            $emailNotificationFailed = ! SafeNotifier::send(
                $dealer->user,
                new DealerApprovedNotification($dealer),
                [
                    'context' => 'dealer_approved',
                    'dealer_id' => $dealer->id,
                    'user_id' => $dealer->user->id,
                ],
            );
        }

        session()->flash(
            $emailNotificationFailed ? 'error' : 'success',
            $emailNotificationFailed
                ? 'Dealer approved, but the approval email could not be sent.'
                : 'Dealer approved successfully.',
        );
    }

    public function reject(int $dealerId): void
    {
        $this->authorizePermission('dealers.update');

        Dealer::findOrFail($dealerId)->update(['approval_status' => 'rejected']);
        session()->flash('success', 'Dealer rejected.');
    }

    public function markInactive(int $dealerId): void
    {
        $this->authorizePermission('dealers.update');

        Dealer::findOrFail($dealerId)->update(['approval_status' => 'inactive']);
        session()->flash('success', 'Dealer marked inactive.');
    }

    public function delete(int $dealerId): void
    {
        $this->authorizePermission('dealers.delete');

        Dealer::findOrFail($dealerId)->delete();
        session()->flash('success', 'Dealer moved to trash.');
    }

    public function restore(int $dealerId): void
    {
        $this->authorizePermission('dealers.delete');

        Dealer::onlyTrashed()->findOrFail($dealerId)->restore();
        session()->flash('success', 'Dealer restored successfully.');
    }

    public function forceDelete(int $dealerId): void
    {
        $this->authorizePermission('dealers.delete');

        Dealer::onlyTrashed()->findOrFail($dealerId)->forceDelete();
        session()->flash('success', 'Dealer permanently deleted.');
    }

    public function openPasswordForm(int $dealerId): void
    {
        $this->authorizePermission('dealers.update');

        $dealer = Dealer::with('user')->findOrFail($dealerId);

        $this->resetPasswordForm();
        $this->passwordDealerId = $dealer->id;
        $this->loginEmail = $dealer->user?->email ?? $this->defaultDealerLoginEmail($dealer);
        $this->isPasswordFormOpen = true;
    }

    public function savePassword(): void
    {
        $this->authorizePermission('dealers.update');

        $validated = $this->validate([
            'passwordDealerId' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
        ]);

        $dealer = Dealer::with('user')->findOrFail($validated['passwordDealerId']);

        DB::transaction(function () use ($dealer, $validated) {
            $user = $dealer->user;

            if (! $user) {
                $user = User::create([
                    'name' => $dealer->name,
                    'email' => $this->uniqueDealerLoginEmail($dealer),
                    'password' => Hash::make($validated['password']),
                    'role' => 'dealer',
                ]);

                $dealer->update(['user_id' => $user->id]);

                return;
            }

            $user->update([
                'name' => $dealer->name,
                'password' => Hash::make($validated['password']),
            ]);
        });

        session()->flash('success', 'Dealer password updated.');
        $this->resetPasswordForm();
        $this->isPasswordFormOpen = false;
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function cancelPassword(): void
    {
        $this->resetPasswordForm();
        $this->isPasswordFormOpen = false;
    }

    public function render(): View
    {
        $dealers = Dealer::query()
            ->with(['zone', 'user'])
            ->when($this->showTrashed, fn ($query) => $query->withTrashed())
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('dealer_code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('mobile', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->zone !== '', fn ($query) => $query->where('zone_id', $this->zone))
            ->when($this->approvalStatus !== '', fn ($query) => $query->where('approval_status', $this->approvalStatus))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.dealers.index', [
            'dealers' => $dealers,
            'zones' => Zone::orderBy('name')->pluck('name', 'id'),
            'activeZones' => Zone::active()->orderBy('name')->pluck('name', 'id'),
            'users' => User::orderBy('name')->pluck('name', 'id'),
            'approvalStatusLabels' => Dealer::approvalStatusLabels(),
            'stats' => $this->stats(),
            'permissions' => [
                'create' => auth()->user()?->can('dealers.create') ?? false,
                'update' => auth()->user()?->can('dealers.update') ?? false,
                'delete' => auth()->user()?->can('dealers.delete') ?? false,
                'view' => auth()->user()?->can('dealers.view') ?? false,
            ],
        ])->layout('admin.layouts.app', ['title' => 'Dealers']);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }

    private function normalizeNullableFields(): void
    {
        foreach (['email', 'zone_id', 'approved_by', 'approved_at', 'user_id'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->name = '';
        $this->mobile = '';
        $this->email = null;
        $this->address = null;
        $this->zone_id = null;
        $this->approval_status = 'pending';
        $this->opening_balance = 0;
        $this->current_due = 0;
        $this->approved_by = null;
        $this->approved_at = null;
        $this->user_id = null;
    }

    private function resetPasswordForm(): void
    {
        $this->resetValidation();
        $this->passwordDealerId = null;
        $this->loginEmail = '';
        $this->password = '';
        $this->password_confirmation = '';
    }

    private function defaultDealerLoginEmail(Dealer $dealer): string
    {
        return $dealer->email ?: sprintf('%s-dealer@waterfall.local', $dealer->mobile);
    }

    private function uniqueDealerLoginEmail(Dealer $dealer): string
    {
        $baseEmail = $this->defaultDealerLoginEmail($dealer);

        if (! User::where('email', $baseEmail)->exists()) {
            return $baseEmail;
        }

        [$localPart, $domain] = str_contains($baseEmail, '@')
            ? explode('@', $baseEmail, 2)
            : [$baseEmail, 'waterfall.local'];

        return sprintf('%s+dealer%s@%s', $localPart, $dealer->id, $domain);
    }

    private function stats(): array
    {
        return [
            'total' => Dealer::count(),
            'approved' => Dealer::where('approval_status', 'approved')->count(),
            'pending' => Dealer::where('approval_status', 'pending')->count(),
            'due' => Dealer::sum('current_due'),
        ];
    }
}
