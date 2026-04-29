<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $fillable = [
        'user_id',
        'customer_id',
        'name',
        'name_bn',
        'mobile',
        'email',
        'address',
        'address_bn',
        'zone_id',
        'customer_type',
        'approval_status',
        'default_delivery_slot',
        'opening_balance',
        'current_due',
        'jar_deposit_qty',
        'qr_code',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_due' => 'decimal:2',
        'jar_deposit_qty' => 'integer',
        'approved_at' => 'datetime',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->customer_id)) {
                $customer->customer_id = self::generateCustomerId();
            }
        });
    }

    public static function generateCustomerId(): string
    {
        $max = self::withTrashed()->max('id') ?? 0;

        return 'WF-CUS-'.str_pad($max + 1, 6, '0', STR_PAD_LEFT);
    }

    public function qrValue(): string
    {
        if (filled($this->customer_id)) {
            return $this->customer_id;
        }

        // TODO: If legacy customers without customer_id exist, backfill them through
        // the existing customer ID generation rule. For QR display, keep a stable fallback.
        return 'WF-CUS-'.str_pad((string) $this->getKey(), 6, '0', STR_PAD_LEFT);
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

    public function customerPrices(): HasMany
    {
        return $this->hasMany(CustomerPrice::class);
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CustomerSubscription::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(CustomerDeviceToken::class);
    }

    public function customerNotifications(): HasMany
    {
        return $this->hasMany(CustomerNotification::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(CustomerSubscription::class)
            ->whereIn('status', ['active', 'paused'])
            ->latest();
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
        return $query->whereIn('approval_status', ['approved']);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public static function typeLabels(): array
    {
        return [
            'residential' => 'Residential',
            'corporate' => 'Corporate',
        ];
    }

    public static function approvalStatusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'inactive' => 'Inactive',
        ];
    }

    public static function deliverySlotLabels(): array
    {
        return [
            'now' => 'Now',
            'morning' => 'Morning',
            'afternoon' => 'Afternoon',
            'evening' => 'Evening',
            'custom' => 'Custom',
        ];
    }

    /**
     * Recalculate current_due as:
     *   total of all delivered order amounts
     *   minus all accepted payments made by this customer
     *
     * This works whether or not invoices have been raised, because invoices
     * are monthly aggregates and may not exist yet for recent deliveries.
     */
    public function recalculateCurrentDue(): void
    {
        $totalOrdered = (float) $this->orders()
            ->where('order_status', 'delivered')
            ->sum('total_amount');

        $totalPaid = (float) Payment::where('customer_id', $this->id)
            ->where('payment_type', 'customer')
            ->where('collection_status', 'accepted')
            ->sum('amount');

        $this->update(['current_due' => max(0, $totalOrdered - $totalPaid)]);
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
