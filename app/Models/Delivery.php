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

        $this->order->update(['order_status' => 'assigned']);
    }

    public function markInProgress(): void
    {
        $this->update(['delivery_status' => 'in_progress']);
        $this->order->update(['order_status' => 'assigned']);
    }

    public function markDelivered(): void
    {
        $this->update([
            'delivery_status' => 'delivered',
            'delivered_at'    => now(),
        ]);

        $this->order->update(['order_status' => 'delivered']);
    }

    public function markFailed(?string $reason = null): void
    {
        $this->update([
            'delivery_status' => 'failed',
            'failure_reason'  => $reason ?? $this->failure_reason,
        ]);
        // Keep order as assigned so it can be re-assigned
        $this->order->update(['order_status' => 'assigned']);
    }

    public function markCancelled(): void
    {
        $previousOrderStatus = $this->order->order_status;

        $this->update(['delivery_status' => 'cancelled']);

        // Revert order to confirmed if it was assigned/in_progress
        if (in_array($previousOrderStatus, ['assigned'])) {
            $this->order->update(['order_status' => 'confirmed']);
        }
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
