<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_no',
        'invoice_type',
        'customer_id',
        'dealer_id',
        'billing_month',
        'billing_year',
        'invoice_date',
        'due_date',
        'subtotal',
        'previous_due',
        'total_amount',
        'paid_amount',
        'due_amount',
        'invoice_status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'invoice_date'  => 'date',
        'due_date'      => 'date',
        'subtotal'      => 'decimal:2',
        'previous_due'  => 'decimal:2',
        'total_amount'  => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'due_amount'    => 'decimal:2',
        'billing_month' => 'integer',
        'billing_year'  => 'integer',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_no)) {
                $invoice->invoice_no = self::generateInvoiceNo();
            }
        });
    }

    public static function generateInvoiceNo(): string
    {
        $max = self::withTrashed()->max('id') ?? 0;
        return 'WF-INV-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
    }

    // ── Relationships ──────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('invoice_status', 'draft');
    }

    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('invoice_status', 'issued');
    }

    public function scopePartial(Builder $query): Builder
    {
        return $query->where('invoice_status', 'partial');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('invoice_status', 'paid');
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('invoice_type', 'customer')->where('customer_id', $customerId);
    }

    public function scopeForDealer(Builder $query, int $dealerId): Builder
    {
        return $query->where('invoice_type', 'dealer')->where('dealer_id', $dealerId);
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Recalculate total_amount and due_amount from current fields.
     * Does NOT save — call save() or update() after.
     */
    public function recalculateTotals(): void
    {
        $total = (float) $this->subtotal + (float) $this->previous_due;
        $due   = max(0, $total - (float) $this->paid_amount);

        $this->total_amount = $total;
        $this->due_amount   = $due;
    }

    /**
     * Sync invoice_status based on current paid/due amounts.
     * Does NOT save — call save() or update() after.
     */
    public function syncStatus(): void
    {
        if ($this->invoice_status === 'cancelled') {
            return;
        }

        $total = (float) $this->total_amount;
        $paid  = (float) $this->paid_amount;
        $due   = (float) $this->due_amount;

        if ($total > 0 && $due <= 0) {
            $this->invoice_status = 'paid';
        } elseif ($paid > 0 && $due > 0) {
            $this->invoice_status = 'partial';
        } elseif ($paid <= 0 && $this->invoice_status !== 'draft') {
            $this->invoice_status = 'issued';
        }
    }

    /**
     * Recalculate paid_amount from actual payments, then totals and status.
     * Saves the invoice.
     */
    public function syncFromPayments(): void
    {
        $paid = (float) $this->payments()->sum('amount');

        $this->paid_amount = $paid;
        $this->recalculateTotals();
        $this->syncStatus();
        $this->save();
    }

    /**
     * Update the related customer or dealer current_due.
     */
    public function updatePartyDue(): void
    {
        if ($this->invoice_type === 'customer' && $this->customer_id) {
            Customer::find($this->customer_id)?->recalculateCurrentDue();
        } elseif ($this->invoice_type === 'dealer' && $this->dealer_id) {
            Dealer::find($this->dealer_id)?->recalculateCurrentDue();
        }
    }

    public function markIssued(): void
    {
        $this->recalculateTotals();
        if ($this->invoice_status === 'draft') {
            $this->invoice_status = 'issued';
        }
        $this->syncStatus();
        $this->save();
        $this->updatePartyDue();
    }

    public function markCancelled(): void
    {
        $this->invoice_status = 'cancelled';
        $this->save();
        $this->updatePartyDue();
    }

    // ── Label helpers ──────────────────────────────────────────────

    public static function typeLabels(): array
    {
        return ['customer' => 'Customer', 'dealer' => 'Dealer'];
    }

    public static function statusLabels(): array
    {
        return [
            'draft'     => 'Draft',
            'issued'    => 'Issued',
            'partial'   => 'Partial',
            'paid'      => 'Paid',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function monthLabels(): array
    {
        return [
            1 => 'January', 2 => 'February', 3 => 'March',
            4 => 'April',   5 => 'May',       6 => 'June',
            7 => 'July',    8 => 'August',    9 => 'September',
            10 => 'October', 11 => 'November', 12 => 'December',
        ];
    }
}
