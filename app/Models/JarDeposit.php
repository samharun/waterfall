<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JarDeposit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'deposit_no',
        'party_type',
        'customer_id',
        'dealer_id',
        'product_id',
        'transaction_type',
        'adjustment_direction',
        'quantity',
        'deposit_amount',
        'transaction_date',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity'         => 'integer',
        'deposit_amount'   => 'decimal:2',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (JarDeposit $jd) {
            if (empty($jd->deposit_no)) {
                $jd->deposit_no = self::generateDepositNo();
            }
        });

        $sync = function (JarDeposit $jd) {
            if ($jd->party_type === 'customer' && $jd->customer_id) {
                Customer::find($jd->customer_id)?->recalculateJarDepositQty();
            } elseif ($jd->party_type === 'dealer' && $jd->dealer_id) {
                Dealer::find($jd->dealer_id)?->recalculateJarDepositQty();
            }
        };

        static::created($sync);
        static::updated($sync);
        static::deleted($sync);
        static::restored($sync);
    }

    public static function generateDepositNo(): string
    {
        $max = self::withTrashed()->max('id') ?? 0;
        return 'WF-JDEP-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('party_type', 'customer')->where('customer_id', $customerId);
    }

    public function scopeForDealer(Builder $query, int $dealerId): Builder
    {
        return $query->where('party_type', 'dealer')->where('dealer_id', $dealerId);
    }

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeDepositReceived(Builder $query): Builder
    {
        return $query->where('transaction_type', 'deposit_received');
    }

    public function scopeJarIssued(Builder $query): Builder
    {
        return $query->where('transaction_type', 'jar_issued');
    }

    public function scopeJarReturned(Builder $query): Builder
    {
        return $query->where('transaction_type', 'jar_returned');
    }

    // ── Label helpers ──────────────────────────────────────────────

    public static function typeLabels(): array
    {
        return [
            'deposit_received' => 'Deposit Received',
            'jar_issued'       => 'Jar Issued',
            'jar_returned'     => 'Jar Returned',
            'adjustment'       => 'Adjustment',
        ];
    }

    public static function directionLabels(): array
    {
        return [
            'increase' => 'Increase',
            'decrease' => 'Decrease',
        ];
    }

    public static function partyTypeLabels(): array
    {
        return [
            'customer' => 'Customer',
            'dealer'   => 'Dealer',
        ];
    }

    /**
     * Calculate the signed quantity effect of this transaction.
     */
    public function signedQuantity(): int
    {
        return match ($this->transaction_type) {
            'deposit_received', 'jar_issued'                          => $this->quantity,
            'jar_returned'                                            => -$this->quantity,
            'adjustment' => $this->adjustment_direction === 'decrease'
                ? -$this->quantity
                : $this->quantity,
            default => 0,
        };
    }
}
