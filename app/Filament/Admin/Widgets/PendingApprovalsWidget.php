<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

/**
 * Medium Priority Widget 1: Pending Customer Approvals
 *
 * Shows pending customers with quick approve/reject actions.
 * Saves admin from navigating to the customer list for routine approvals.
 */
class PendingApprovalsWidget extends Widget
{
    protected static ?int $sort = 6;

    protected string $view = 'filament.admin.widgets.pending-approvals';

    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->can('customers.view') && $user?->can('customers.approve');
    }

    public function getPendingCustomers()
    {
        return Customer::with('zone')
            ->where('approval_status', 'pending')
            ->orderBy('created_at', 'asc') // oldest first — most urgent
            ->limit(10)
            ->get();
    }

    public function getTotalPending(): int
    {
        return Customer::where('approval_status', 'pending')->count();
    }

    public function approve(int $customerId): void
    {
        $customer = Customer::find($customerId);

        if (! $customer || $customer->approval_status !== 'pending') {
            return;
        }

        if (! $customer->zone_id || ! $customer->address) {
            Notification::make()
                ->title('Cannot approve')
                ->body('Customer must have a zone and address before approval.')
                ->warning()
                ->send();
            return;
        }

        $customer->update([
            'approval_status' => 'approved',
            'approved_by'     => Auth::id(),
            'approved_at'     => now(),
        ]);

        Notification::make()
            ->title('Customer approved')
            ->success()
            ->send();
    }

    public function reject(int $customerId): void
    {
        $customer = Customer::find($customerId);

        if (! $customer || $customer->approval_status !== 'pending') {
            return;
        }

        $customer->update(['approval_status' => 'rejected']);

        Notification::make()
            ->title('Customer rejected')
            ->warning()
            ->send();
    }
}
