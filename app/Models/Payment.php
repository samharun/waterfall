<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_no',
        'payment_type',
        'customer_id',
        'dealer_id',
        'invoice_id',
        'order_id',
        'delivery_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'received_by',
        'collection_source',
        'collection_status',
        'collected_at',
        'remarks',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
        'collected_at' => 'datetime',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->payment_no)) {
                $payment->payment_no = self::generatePaymentNo();
            }
        });

        // After any write operation, sync invoice and party due
        $sync = function (Payment $payment) {
            $payment->syncInvoiceAfterPayment();
            $payment->syncPartyDueAfterPayment();
        };

        static::created($sync);
        static::updated($sync);
        static::deleted($sync);
        static::restored($sync);
    }

    public static function generatePaymentNo(): string
    {
        $max = self::withTrashed()->max('id') ?? 0;
        return 'WF-PAY-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('payment_type', 'customer')->where('customer_id', $customerId);
    }

    public function scopeForDealer(Builder $query, int $dealerId): Builder
    {
        return $query->where('payment_type', 'dealer')->where('dealer_id', $dealerId);
    }

    public function scopeCollectedByDeliveryStaff(Builder $query): Builder
    {
        return $query->where('collection_source', 'delivery_staff');
    }

    public function scopeForDelivery(Builder $query, int $deliveryId): Builder
    {
        return $query->where('delivery_id', $deliveryId);
    }

    public function scopeForOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('collection_status', 'accepted');
    }

    // ── Sync helpers ───────────────────────────────────────────────

    public function syncInvoiceAfterPayment(): void
    {
        if ($this->invoice_id) {
            // Reload fresh to avoid stale state
            $invoice = Invoice::find($this->invoice_id);
            $invoice?->syncFromPayments();
        }
    }

    public function syncPartyDueAfterPayment(): void
    {
        if ($this->payment_type === 'customer' && $this->customer_id) {
            Customer::find($this->customer_id)?->recalculateCurrentDue();
        } elseif ($this->payment_type === 'dealer' && $this->dealer_id) {
            Dealer::find($this->dealer_id)?->recalculateCurrentDue();
        }
    }

    // ── Label helpers ──────────────────────────────────────────────

    public static function methodLabels(): array
    {
        return [
            'cash'  => 'Cash',
            'bkash' => 'bKash',
            'nagad' => 'Nagad',
            'bank'  => 'Bank Transfer',
            'card'  => 'Card',
            'other' => 'Other',
        ];
    }

    public static function typeLabels(): array
    {
        return ['customer' => 'Customer', 'dealer' => 'Dealer'];
    }

    public static function collectionSourceLabels(): array
    {
        return [
            'admin'          => 'Admin',
            'delivery_staff' => 'Delivery Staff',
            'customer_panel' => 'Customer Panel',
            'dealer_panel'   => 'Dealer Panel',
        ];
    }

    public static function collectionStatusLabels(): array
    {
        return [
            'accepted'       => 'Accepted',
            'pending_review' => 'Pending Review',
            'rejected'       => 'Rejected',
        ];
    }
}
