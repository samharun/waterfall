<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_no',
        'order_type',
        'customer_id',
        'dealer_id',
        'subscription_id',
        'zone_id',
        'ordered_by',
        'preferred_delivery_slot',
        'preferred_delivery_time',
        'order_date',
        'subtotal',
        'discount',
        'delivery_charge',
        'total_amount',
        'payment_status',
        'order_status',
        'remarks',
    ];

    protected $casts = [
        'order_date'              => 'date',
        'preferred_delivery_time' => 'datetime',
        'subtotal'                => 'decimal:2',
        'discount'                => 'decimal:2',
        'delivery_charge'         => 'decimal:2',
        'total_amount'            => 'decimal:2',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_no)) {
                $order->order_no = self::generateOrderNo();
            }
        });

        // Prevent manually setting delivery-driven statuses without a delivery record.
        static::updating(function (Order $order) {
            if (! $order->isDirty('order_status')) {
                return;
            }

            $newStatus = $order->order_status;

            if (in_array($newStatus, ['assigned', 'delivered'])) {
                $hasDelivery = $order->deliveries()->whereNotIn('delivery_status', ['cancelled'])->exists();

                if (! $hasDelivery) {
                    throw new \RuntimeException(
                        "Order status cannot be set to \"{$newStatus}\" without an active delivery assignment."
                    );
                }
            }
        });
    }

    public static function generateOrderNo(): string
    {
        $max = self::withTrashed()->max('id') ?? 0;
        return 'WF-ORD-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
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

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class, 'subscription_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('order_status', 'pending');
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('order_status', 'confirmed');
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('order_status', 'delivered');
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('order_type', 'customer')->where('customer_id', $customerId);
    }

    public function scopeForDealer(Builder $query, int $dealerId): Builder
    {
        return $query->where('order_type', 'dealer')->where('dealer_id', $dealerId);
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Recalculate subtotal and total_amount from loaded items.
     * Call after items are saved.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');
        $total    = max(0, $subtotal - $this->discount + $this->delivery_charge);

        $this->update([
            'subtotal'     => $subtotal,
            'total_amount' => $total,
        ]);
    }

    public function markConfirmed(): void
    {
        $this->update(['order_status' => 'confirmed']);
    }

    public function markCancelled(): void
    {
        $this->update(['order_status' => 'cancelled']);
    }

    public function canCreateDelivery(): bool
    {
        return in_array($this->order_status, ['confirmed', 'assigned'])
            && ! $this->deliveries()->active()->exists();
    }

    // ── Label helpers ──────────────────────────────────────────────

    public static function orderTypeLabels(): array
    {
        return ['customer' => 'Customer', 'dealer' => 'Dealer'];
    }

    public static function deliverySlotLabels(): array
    {
        return [
            'now'       => 'Now',
            'morning'   => 'Morning',
            'afternoon' => 'Afternoon',
            'evening'   => 'Evening',
            'custom'    => 'Custom',
        ];
    }

    public static function orderStatusLabels(): array
    {
        return [
            'pending'   => 'Pending',
            'confirmed' => 'Confirmed',
            'assigned'  => 'Assigned',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function paymentStatusLabels(): array
    {
        return [
            'unpaid'  => 'Unpaid',
            'partial' => 'Partial',
            'paid'    => 'Paid',
        ];
    }
}
