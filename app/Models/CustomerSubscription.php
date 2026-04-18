<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class CustomerSubscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subscription_no',
        'customer_id',
        'product_id',
        'quantity',
        'frequency',
        'delivery_days',
        'preferred_delivery_slot',
        'preferred_delivery_time',
        'start_date',
        'next_delivery_date',
        'paused_from',
        'paused_to',
        'pause_reason',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'delivery_days'      => 'array',
        'start_date'         => 'date',
        'next_delivery_date' => 'date',
        'paused_from'        => 'date',
        'paused_to'          => 'date',
        'quantity'           => 'integer',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (CustomerSubscription $sub) {
            if (empty($sub->subscription_no)) {
                $sub->subscription_no = self::generateSubscriptionNo();
            }
        });
    }

    public static function generateSubscriptionNo(): string
    {
        $max = self::withTrashed()->max('id') ?? 0;
        return 'WF-SUB-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
    }

    // ── Relationships ──────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'subscription_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePaused(Builder $query): Builder
    {
        return $query->where('status', 'paused');
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeDueForDelivery(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('next_delivery_date')
            ->whereDate('next_delivery_date', '<=', today());
    }

    // ── Status helpers ─────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function pause(?string $reason = null, ?string $pausedFrom = null, ?string $pausedTo = null): void
    {
        $this->update([
            'status'       => 'paused',
            'paused_from'  => $pausedFrom ?? today()->toDateString(),
            'paused_to'    => $pausedTo,
            'pause_reason' => $reason,
        ]);
    }

    public function resume(): void
    {
        $this->update([
            'status'       => 'active',
            'paused_from'  => null,
            'paused_to'    => null,
            'pause_reason' => null,
        ]);

        // Recalculate next delivery date from today
        $next = $this->calculateNextDeliveryDate(today());
        $this->update(['next_delivery_date' => $next?->toDateString()]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Advance next_delivery_date by one period after a recurring order is generated.
     */
    public function advanceNextDeliveryDate(): void
    {
        $next = $this->calculateNextDeliveryDate(
            ($this->next_delivery_date ?? today())->copy()->addDay()
        );
        $this->update(['next_delivery_date' => $next?->toDateString()]);
    }

    /**
     * Calculate the next delivery date based on frequency and delivery_days.
     */
    public function calculateNextDeliveryDate(?Carbon $fromDate = null): ?Carbon
    {
        $from = $fromDate ?? ($this->start_date ?? today());

        return match ($this->frequency) {
            'daily'       => $this->nextDaily($from),
            'weekly'      => $this->nextWeekly($from),
            'custom_days' => $this->nextCustomDays($from),
            'monthly'     => $this->nextMonthly($from),
            default       => $from,
        };
    }

    private function nextDaily(Carbon $from): Carbon
    {
        return $from->isFuture() || $from->isToday() ? $from->copy() : today()->addDay();
    }

    private function nextWeekly(Carbon $from): Carbon
    {
        $days = $this->delivery_days ?? [];

        if (empty($days)) {
            return $from->isFuture() || $from->isToday() ? $from->copy() : $from->copy()->addWeek();
        }

        return $this->nextMatchingDay($from, $days);
    }

    private function nextCustomDays(Carbon $from): Carbon
    {
        $days = $this->delivery_days ?? [];

        if (empty($days)) {
            return $from->isFuture() || $from->isToday() ? $from->copy() : today();
        }

        return $this->nextMatchingDay($from, $days);
    }

    private function nextMonthly(Carbon $from): Carbon
    {
        if ($from->isFuture() || $from->isToday()) {
            return $from->copy();
        }

        $next = today()->copy()->day(min($from->day, today()->daysInMonth));

        if ($next->isPast()) {
            $next->addMonth();
            $next->day(min($from->day, $next->daysInMonth));
        }

        return $next;
    }

    private function nextMatchingDay(Carbon $from, array $days): Carbon
    {
        $dayMap = [
            'sunday'    => Carbon::SUNDAY,
            'monday'    => Carbon::MONDAY,
            'tuesday'   => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday'  => Carbon::THURSDAY,
            'friday'    => Carbon::FRIDAY,
            'saturday'  => Carbon::SATURDAY,
        ];

        $dayNumbers = array_filter(array_map(fn ($d) => $dayMap[strtolower($d)] ?? null, $days));

        if (empty($dayNumbers)) {
            return $from->copy();
        }

        $candidate = $from->isFuture() || $from->isToday() ? $from->copy() : today()->copy();

        for ($i = 0; $i <= 7; $i++) {
            if (in_array($candidate->dayOfWeek, $dayNumbers)) {
                return $candidate;
            }
            $candidate->addDay();
        }

        return $from->copy();
    }

    // ── Label helpers ──────────────────────────────────────────────

    public static function frequencyLabels(): array
    {
        return [
            'daily'       => 'Daily',
            'weekly'      => 'Weekly',
            'custom_days' => 'Custom Days',
            'monthly'     => 'Monthly',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'active'    => 'Active',
            'paused'    => 'Paused',
            'cancelled' => 'Cancelled',
            'inactive'  => 'Inactive',
        ];
    }

    public static function deliveryDayOptions(): array
    {
        return [
            'saturday'  => 'Saturday',
            'sunday'    => 'Sunday',
            'monday'    => 'Monday',
            'tuesday'   => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday'  => 'Thursday',
            'friday'    => 'Friday',
        ];
    }

    public static function slotLabels(): array
    {
        return [
            'morning'   => 'Morning',
            'afternoon' => 'Afternoon',
            'evening'   => 'Evening',
            'custom'    => 'Custom',
        ];
    }
}
