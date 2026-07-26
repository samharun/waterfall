<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer;
use App\Models\User;
use App\Models\Zone;
use App\Notifications\CustomerApprovedNotification;
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
    public string $customerType = '';
    public string $approvalStatus = '';
    public bool $showTrashed = false;

    public bool $isFormOpen = false;
    public ?int $editingId = null;

    public string $name = '';
    public ?string $name_bn = null;
    public string $mobile = '';
    public ?string $email = null;
    public string|int|null $zone_id = null;
    public string $customer_type = 'residential';
    public string $approval_status = 'pending';
    public ?string $default_delivery_slot = null;
    public ?string $address = null;
    public ?string $address_bn = null;
    public string|float|int $opening_balance = 0;
    public string|float|int $current_due = 0;
    public string|int $jar_deposit_qty = 0;
    public string|int|null $approved_by = null;
    public ?string $approved_at = null;
    public string|int|null $user_id = null;
    public ?string $qr_code = null;

    public bool $isPasswordFormOpen = false;
    public ?int $passwordCustomerId = null;
    public string $loginEmail = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('customers.view'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingZone(): void
    {
        $this->resetPage();
    }

    public function updatingCustomerType(): void
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
        $this->authorizePermission('customers.create');
        $this->resetForm();
        $this->isFormOpen = true;
    }

    public function edit(int $customerId): void
    {
        $this->authorizePermission('customers.update');

        $customer = Customer::withTrashed()->findOrFail($customerId);

        $this->editingId = $customer->id;
        $this->name = $customer->name;
        $this->name_bn = $customer->name_bn;
        $this->mobile = $customer->mobile;
        $this->email = $customer->email;
        $this->zone_id = $customer->zone_id;
        $this->customer_type = $customer->customer_type;
        $this->approval_status = $customer->approval_status;
        $this->default_delivery_slot = $customer->default_delivery_slot;
        $this->address = $customer->address;
        $this->address_bn = $customer->address_bn;
        $this->opening_balance = $customer->opening_balance;
        $this->current_due = $customer->current_due;
        $this->jar_deposit_qty = $customer->jar_deposit_qty;
        $this->approved_by = $customer->approved_by;
        $this->approved_at = $customer->approved_at?->format('Y-m-d\TH:i');
        $this->user_id = $customer->user_id;
        $this->qr_code = $customer->qr_code;
        $this->isFormOpen = true;
    }

    public function save(): void
    {
        $this->authorizePermission($this->editingId ? 'customers.update' : 'customers.create');
        $this->normalizeNullableFields();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'mobile' => [
                'required',
                'string',
                'max:11',
                'regex:/^01[3-9][0-9]{8}$/',
                Rule::unique('customers', 'mobile')->ignore($this->editingId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($this->editingId),
            ],
            'zone_id' => ['nullable', 'integer', Rule::exists('zones', 'id')],
            'customer_type' => ['required', Rule::in(array_keys(Customer::typeLabels()))],
            'approval_status' => ['required', Rule::in(array_keys(Customer::approvalStatusLabels()))],
            'default_delivery_slot' => ['nullable', Rule::in(array_keys(Customer::deliverySlotLabels()))],
            'address' => ['nullable', 'string', 'max:500'],
            'address_bn' => ['nullable', 'string', 'max:500'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'current_due' => ['required', 'numeric'],
            'jar_deposit_qty' => ['required', 'integer', 'min:0'],
            'approved_by' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'approved_at' => ['nullable', 'date'],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'qr_code' => ['nullable', 'string', 'max:255'],
        ]);

        Customer::withTrashed()->updateOrCreate(
            ['id' => $this->editingId],
            $validated,
        );

        session()->flash('success', $this->editingId ? 'Customer updated successfully.' : 'Customer created successfully.');

        $this->resetForm();
        $this->isFormOpen = false;
    }

    public function approve(int $customerId): void
    {
        $this->authorizePermission('customers.approve');

        $customer = Customer::findOrFail($customerId);

        if (! $customer->zone_id || ! $customer->address) {
            session()->flash('error', 'Zone and address are required before approving a customer.');
            return;
        }

        $customer->update([
            'approval_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $emailNotificationFailed = false;

        if ($customer->email && $customer->user) {
            $emailNotificationFailed = ! SafeNotifier::send(
                $customer->user,
                new CustomerApprovedNotification($customer),
                [
                    'context' => 'customer_approved',
                    'customer_id' => $customer->id,
                    'user_id' => $customer->user->id,
                ],
            );
        }

        session()->flash(
            $emailNotificationFailed ? 'error' : 'success',
            $emailNotificationFailed
                ? 'Customer approved, but the approval email could not be sent.'
                : 'Customer approved successfully.',
        );
    }

    public function reject(int $customerId): void
    {
        $this->authorizePermission('customers.reject');

        Customer::findOrFail($customerId)->update(['approval_status' => 'rejected']);
        session()->flash('success', 'Customer rejected.');
    }

    public function markInactive(int $customerId): void
    {
        $this->authorizePermission('customers.update');

        Customer::findOrFail($customerId)->update(['approval_status' => 'inactive']);
        session()->flash('success', 'Customer marked inactive.');
    }

    public function delete(int $customerId): void
    {
        $this->authorizePermission('customers.delete');

        Customer::findOrFail($customerId)->delete();
        session()->flash('success', 'Customer moved to trash.');
    }

    public function restore(int $customerId): void
    {
        $this->authorizePermission('customers.delete');

        Customer::onlyTrashed()->findOrFail($customerId)->restore();
        session()->flash('success', 'Customer restored successfully.');
    }

    public function forceDelete(int $customerId): void
    {
        $this->authorizePermission('customers.delete');

        Customer::onlyTrashed()->findOrFail($customerId)->forceDelete();
        session()->flash('success', 'Customer permanently deleted.');
    }

    public function openPasswordForm(int $customerId): void
    {
        $this->authorizePermission('customers.update');

        $customer = Customer::with('user')->findOrFail($customerId);

        $this->resetPasswordForm();
        $this->passwordCustomerId = $customer->id;
        $this->loginEmail = $customer->user?->email ?? $this->defaultCustomerLoginEmail($customer);
        $this->isPasswordFormOpen = true;
    }

    public function savePassword(): void
    {
        $this->authorizePermission('customers.update');

        $validated = $this->validate([
            'passwordCustomerId' => ['required', 'integer', Rule::exists('customers', 'id')],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
        ]);

        $customer = Customer::with('user')->findOrFail($validated['passwordCustomerId']);

        DB::transaction(function () use ($customer, $validated) {
            $user = $customer->user;

            if (! $user) {
                $user = User::create([
                    'name' => $customer->name,
                    'email' => $this->uniqueCustomerLoginEmail($customer),
                    'password' => Hash::make($validated['password']),
                    'role' => 'customer',
                ]);

                $customer->update(['user_id' => $user->id]);

                return;
            }

            $user->update([
                'name' => $customer->name,
                'password' => Hash::make($validated['password']),
            ]);
        });

        session()->flash('success', 'Customer password updated.');
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
        $customers = Customer::query()
            ->with(['zone', 'user'])
            ->when($this->showTrashed, fn ($query) => $query->withTrashed())
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('customer_id', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('name_bn', 'like', '%'.$this->search.'%')
                        ->orWhere('mobile', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->zone !== '', fn ($query) => $query->where('zone_id', $this->zone))
            ->when($this->customerType !== '', fn ($query) => $query->where('customer_type', $this->customerType))
            ->when($this->approvalStatus !== '', fn ($query) => $query->where('approval_status', $this->approvalStatus))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.customers.index', [
            'customers' => $customers,
            'zones' => Zone::orderBy('name')->pluck('name', 'id'),
            'activeZones' => Zone::active()->orderBy('name')->pluck('name', 'id'),
            'users' => User::orderBy('name')->pluck('name', 'id'),
            'typeLabels' => Customer::typeLabels(),
            'approvalStatusLabels' => Customer::approvalStatusLabels(),
            'deliverySlotLabels' => Customer::deliverySlotLabels(),
            'stats' => $this->stats(),
            'permissions' => [
                'create' => auth()->user()?->can('customers.create') ?? false,
                'update' => auth()->user()?->can('customers.update') ?? false,
                'delete' => auth()->user()?->can('customers.delete') ?? false,
                'approve' => auth()->user()?->can('customers.approve') ?? false,
                'reject' => auth()->user()?->can('customers.reject') ?? false,
                'view' => auth()->user()?->can('customers.view') ?? false,
            ],
        ])->layout('admin.layouts.app', ['title' => 'Customers']);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }

    private function normalizeNullableFields(): void
    {
        foreach (['email', 'zone_id', 'default_delivery_slot', 'approved_by', 'approved_at', 'user_id', 'qr_code'] as $field) {
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
        $this->name_bn = null;
        $this->mobile = '';
        $this->email = null;
        $this->zone_id = null;
        $this->customer_type = 'residential';
        $this->approval_status = 'pending';
        $this->default_delivery_slot = null;
        $this->address = null;
        $this->address_bn = null;
        $this->opening_balance = 0;
        $this->current_due = 0;
        $this->jar_deposit_qty = 0;
        $this->approved_by = null;
        $this->approved_at = null;
        $this->user_id = null;
        $this->qr_code = null;
    }

    private function resetPasswordForm(): void
    {
        $this->resetValidation();
        $this->passwordCustomerId = null;
        $this->loginEmail = '';
        $this->password = '';
        $this->password_confirmation = '';
    }

    private function defaultCustomerLoginEmail(Customer $customer): string
    {
        return $customer->email ?: sprintf('%s-customer@waterfall.local', $customer->mobile);
    }

    private function uniqueCustomerLoginEmail(Customer $customer): string
    {
        $baseEmail = $this->defaultCustomerLoginEmail($customer);

        if (! User::where('email', $baseEmail)->exists()) {
            return $baseEmail;
        }

        [$localPart, $domain] = str_contains($baseEmail, '@')
            ? explode('@', $baseEmail, 2)
            : [$baseEmail, 'waterfall.local'];

        return sprintf('%s+customer%s@%s', $localPart, $customer->id, $domain);
    }

    private function stats(): array
    {
        return [
            'total' => Customer::count(),
            'approved' => Customer::where('approval_status', 'approved')->count(),
            'pending' => Customer::where('approval_status', 'pending')->count(),
            'due' => Customer::sum('current_due'),
        ];
    }
}
