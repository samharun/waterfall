<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dealer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'dealer_code',
        'name',
        'mobile',
        'email',
        'address',
        'zone_id',
        'approval_status',
        'opening_balance',
        'current_due',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_due'     => 'decimal:2',
        'approved_at'     => 'datetime',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Dealer $dealer) {
            if (empty($dealer->dealer_code)) {
                $dealer->dealer_code = self::generateDealerCode();
            }
        });
    }

    public static function generateDealerCode(): string
    {
        $max = self::withTrashed()->max('id') ?? 0;
        return 'WF-DLR-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
    }

    // ── Relationships ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dealerPrices(): HasMany
    {
        return $this->hasMany(DealerPrice::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function jarDeposits(): HasMany
    {
        return $this->hasMany(JarDeposit::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved');
    }

    // ── Helpers ────────────────────────────────────────────────────

    public static function approvalStatusLabels(): array
    {
        return [
            'pending'  => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'inactive' => 'Inactive',
        ];
    }

    /**
     * Recalculate current_due from non-cancelled, non-draft invoices.
     */
    public function recalculateCurrentDue(): void
    {
        $due = $this->invoices()
            ->whereNotIn('invoice_status', ['cancelled', 'draft'])
            ->sum('due_amount');

        $this->update(['current_due' => max(0, (float) $due)]);
    }

    /**
     * Recalculate jar_deposit_qty from all non-deleted jar deposit transactions.
     */
    public function recalculateJarDepositQty(): void
    {
        $qty = $this->jarDeposits()->get()->sum(
            fn (JarDeposit $jd) => $jd->signedQuantity()
        );

        $this->update(['jar_deposit_qty' => max(0, $qty)]);
    }
}
