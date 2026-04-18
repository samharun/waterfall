<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_no',
        'product_id',
        'transaction_type',
        'adjustment_direction',
        'quantity',
        'unit_cost',
        'transaction_date',
        'reference_type',
        'reference_id',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity'         => 'integer',
        'unit_cost'        => 'decimal:2',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (StockTransaction $tx) {
            if (empty($tx->transaction_no)) {
                $tx->transaction_no = self::generateTransactionNo();
            }
        });

        $sync = fn (StockTransaction $tx) => Product::find($tx->product_id)?->recalculateCurrentStock();

        static::created($sync);
        static::updated($sync);
        static::deleted($sync);
        static::restored($sync);
    }

    public static function generateTransactionNo(): string
    {
        $max = self::withTrashed()->max('id') ?? 0;
        return 'WF-STK-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
    }

    // ── Relationships ──────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeStockIn(Builder $query): Builder
    {
        return $query->where('transaction_type', 'stock_in');
    }

    public function scopeStockOut(Builder $query): Builder
    {
        return $query->where('transaction_type', 'stock_out');
    }

    public function scopeDamaged(Builder $query): Builder
    {
        return $query->where('transaction_type', 'damaged');
    }

    public function scopeReturned(Builder $query): Builder
    {
        return $query->where('transaction_type', 'returned');
    }

    // ── Label helpers ──────────────────────────────────────────────

    public static function typeLabels(): array
    {
        return [
            'stock_in'   => 'Stock In',
            'stock_out'  => 'Stock Out',
            'adjustment' => 'Adjustment',
            'damaged'    => 'Damaged',
            'returned'   => 'Returned',
        ];
    }

    public static function directionLabels(): array
    {
        return [
            'increase' => 'Increase',
            'decrease' => 'Decrease',
        ];
    }

    /**
     * Calculate the signed quantity effect of this transaction.
     */
    public function signedQuantity(): int
    {
        return match ($this->transaction_type) {
            'stock_in', 'returned'                                    => $this->quantity,
            'stock_out', 'damaged'                                    => -$this->quantity,
            'adjustment' => $this->adjustment_direction === 'decrease'
                ? -$this->quantity
                : $this->quantity,
            default => 0,
        };
    }
}
