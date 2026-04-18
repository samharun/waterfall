<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::approved()->first();
        $admin    = User::where('role', 'admin')->first() ?? User::first();

        if (! $customer) {
            $this->command->warn('No approved customer found. Skipping billing seed.');
            return;
        }

        // ── Sample Invoice ─────────────────────────────────────────
        $invoice = Invoice::firstOrCreate(
            ['invoice_no' => 'WF-INV-000001'],
            [
                'invoice_type'   => 'customer',
                'customer_id'    => $customer->id,
                'billing_month'  => now()->month,
                'billing_year'   => now()->year,
                'invoice_date'   => now()->toDateString(),
                'due_date'       => now()->addDays(15)->toDateString(),
                'subtotal'       => 400.00,
                'previous_due'   => 0.00,
                'total_amount'   => 400.00,
                'paid_amount'    => 0.00,
                'due_amount'     => 400.00,
                'invoice_status' => 'issued',
                'created_by'     => $admin?->id,
            ]
        );

        // ── Sample Partial Payment ─────────────────────────────────
        if ($invoice->paid_amount == 0) {
            Payment::create([
                'payment_no'     => Payment::generatePaymentNo(),
                'payment_type'   => 'customer',
                'customer_id'    => $customer->id,
                'invoice_id'     => $invoice->id,
                'payment_date'   => now()->toDateString(),
                'amount'         => 100.00,
                'payment_method' => 'cash',
                'received_by'    => $admin?->id,
                'remarks'        => 'Sample partial payment',
            ]);
            // Payment model boot auto-syncs invoice and customer due
        }

        $invoice->refresh();
        $this->command->info("Invoice {$invoice->invoice_no}: paid={$invoice->paid_amount}, due={$invoice->due_amount}, status={$invoice->invoice_status}");
        $customer->refresh();
        $this->command->info("Customer {$customer->customer_id} current_due: {$customer->current_due}");
    }
}
