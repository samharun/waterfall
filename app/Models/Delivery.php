<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'delivery_no',
        'order_id',
        'zone_id',
        'delivery_staff_id',
        'assigned_by',
        'assigned_at',
        'delivered_at',
        'delivery_status',
        'delivery_note',
        'failure_reason',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Delivery $delivery) {
            if (empty($delivery->delivery_no)) {
                $delivery->delivery_no = self::generateDeliveryNo();
            }
        });

        static::created(function (Delivery $delivery) {
            self::syncOrderAndDue($delivery);
        });

        static::updated(function (Delivery $delivery) {
            if (! $delivery->wasChanged('delivery_status')) {
                return;
            }
            self::syncOrderAndDue($delivery);
        });

        // When a delivery is soft-deleted, revert the order status
        static::deleted(function (Delivery $delivery) {
            $order = $delivery->order()->with(['customer', 'dealer'])->first();
            if (! $order) {
                return;
            }

            // Only revert if this was the active delivery driving the order status
            if (in_array($order->order_status, ['assigned', 'delivered'])) {
                // Check if any other active delivery still exists
                $hasOtherActive = $order->deliveries()
                    ->whereNotIn('delivery_status', ['cancelled'])
                    ->where('id', '!=', $delivery->id)
                    ->exists();

                if (! $hasOtherActive) {
                    $order->updateQuietly(['order_status' => 'confirmed']);
                }
            }

            // Recalculate due since a delivered delivery was removed
            if ($delivery->delivery_status === 'delivered') {
                if ($order->order_type === 'customer') {
                    $order->customer?->recalculateCurrentDue();
                } else {
                    $order->dealer?->recalculateCurrentDue();
                }
            }
        });

        // When a delivery is restored, re-sync order status
        static::restored(function (Delivery $delivery) {
            self::syncOrderAndDue($delivery);
        });
    }

    private static function syncOrderAndDue(Delivery $delivery): void
    {
        $order = $delivery->order()->with(['customer', 'dealer'])->first();
        if (! $order) {
            return;
        }

        $newOrderStatus = match ($delivery->delivery_status) {
            'assigned', 'in_progress' => 'assigned',
            'delivered'               => 'delivered',
            'cancelled'               => in_array($order->order_status, ['assigned']) ? 'confirmed' : null,
            default                   => null,
        };

        if ($newOrderStatus) {
            $order->updateQuietly(['order_status' => $newOrderStatus]);
        }

        // Recalculate due whenever delivery reaches a terminal or delivery state
        if (in_array($delivery->delivery_status, ['delivered', 'cancelled', 'failed'])) {
            if ($order->order_type === 'customer') {
                $order->customer?->recalculateCurrentDue();
            } else {
                $order->dealer?->recalculateCurrentDue();
            }
        }
    }

    public static function generateDeliveryNo(): string
    {
        $max = self::withTrashed()->max('id') ?? 0;
        return 'WF-DEL-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
    }

    // ── Relationships ──────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function deliveryStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_staff_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function totalCollectedAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function hasPaymentCollection(): bool
    {
        return $this->payments()->exists();
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('delivery_status', 'pending');
    }

    public function scopeAssigned(Builder $query): Builder
    {
        return $query->where('delivery_status', 'assigned');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('delivery_status', 'in_progress');
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('delivery_status', 'delivered');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('delivery_status', 'failed');
    }

    public function scopeForStaff(Builder $query, int $userId): Builder
    {
        return $query->where('delivery_staff_id', $userId);
    }

    public function scopeForZone(Builder $query, int $zoneId): Builder
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('delivery_status', ['cancelled', 'delivered']);
    }

    // ── Status helpers ─────────────────────────────────────────────

    public function markAssigned(?int $staffId = null, ?int $assignedById = null): void
    {
        $this->update([
            'delivery_status'   => 'assigned',
            'delivery_staff_id' => $staffId ?? $this->delivery_staff_id,
            'assigned_by'       => $assignedById ?? $this->assigned_by,
            'assigned_at'       => $this->assigned_at ?? now(),
        ]);
        // order status handled by booted() updated hook
    }

    public function markInProgress(): void
    {
        $this->update(['delivery_status' => 'in_progress']);
        // order status handled by booted() updated hook
    }

    public function markDelivered(): void
    {
        $this->update([
            'delivery_status' => 'delivered',
            'delivered_at'    => now(),
        ]);
        // order status + due recalc handled by booted() updated hook
    }

    public function markFailed(?string $reason = null): void
    {
        $this->update([
            'delivery_status' => 'failed',
            'failure_reason'  => $reason ?? $this->failure_reason,
        ]);
        // order status + due recalc handled by booted() updated hook
    }

    public function markCancelled(): void
    {
        $this->update(['delivery_status' => 'cancelled']);
        // order status + due recalc handled by booted() updated hook
    }

    // ── Label helpers ──────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'pending'     => 'Pending',
            'assigned'    => 'Assigned',
            'in_progress' => 'In Progress',
            'delivered'   => 'Delivered',
            'failed'      => 'Failed',
            'cancelled'   => 'Cancelled',
        ];
    }
}
