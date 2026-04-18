<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPrice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'product_id',
        'custom_price',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected $casts = [
        'custom_price'   => 'decimal:2',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCurrentlyEffective(Builder $query, ?string $date = null): Builder
    {
        $date = $date ?? now()->toDateString();

        return $query->where('status', 'active')
            ->where(fn (Builder $q) => $q
                ->whereNull('effective_from')
                ->orWhere('effective_from', '<=', $date)
            )
            ->where(fn (Builder $q) => $q
                ->whereNull('effective_to')
                ->orWhere('effective_to', '>=', $date)
            );
    }

    // ── Helpers ────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'active'   => 'Active',
            'inactive' => 'Inactive',
        ];
    }
}
