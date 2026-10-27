<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Accounts\CollectionAccountingService;
use Illuminate\Validation\ValidationException;

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
        'collected_from_staff_id',
        'remarks',
        'payment_account_id',
    ];

    protected $attributes = [
        'accounting_enabled' => true,
        'collection_status' => 'accepted',
        'collection_source' => 'admin',
        'payment_method' => 'cash',
    ];

    protected $casts = [
        'accounting_enabled' => 'boolean',
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
        'collected_at' => 'datetime',
    ];

    // ── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->payment_no)) {
                $payment->payment_no = self::generatePaymentNo($payment->payment_date);
            }
        });

        // After any write operation, sync invoice and party due
        $sync = function (Payment $payment) {
            app(CollectionAccountingService::class)->sync($payment);
            foreach (array_unique(array_filter([$payment->invoice_id, $payment->getOriginal('invoice_id')])) as $id) {
                Invoice::find($id)?->syncFromPayments();
            }
            foreach (array_unique(array_filter([$payment->customer_id, $payment->getOriginal('customer_id')])) as $id) {
                Customer::find($id)?->recalculateCurrentDue();
            }
            foreach (array_unique(array_filter([$payment->dealer_id, $payment->getOriginal('dealer_id')])) as $id) {
                Dealer::find($id)?->recalculateCurrentDue();
            }
        };

        static::saved($sync);
        static::deleted($sync);
        static::forceDeleting(function () {
            throw ValidationException::withMessages(['payment' => 'Payments must be retained for audit. Use Delete instead of permanent deletion.']);
        });
    }

    // Keep the payment, customer due, invoice and account posting in one commit,
    // including callers which do not start their own database transaction.
    public function save(array $options = [])
    {
        return DB::transaction(function () use ($options) {
            if ($this->exists) {
                $changes = $this->getDirty();
                $fresh = static::withTrashed()->lockForUpdate()->findOrFail($this->getKey());
                $this->setRawAttributes($fresh->getAttributes(), true);
                $this->forceFill($changes);
            }
            $this->lockRelatedBalances();
            $this->validateCollection();

            return parent::save($options);
        });
    }

    public function delete()
    {
        return DB::transaction(function () {
            if (! $this->exists) {
                return null;
            }
            $fresh = static::withTrashed()->lockForUpdate()->findOrFail($this->getKey());
            $this->setRawAttributes($fresh->getAttributes(), true);
            $this->lockRelatedBalances();

            return parent::delete();
        });
    }

    protected function lockRelatedBalances(): void
    {
        foreach (['customer_id' => Customer::class, 'dealer_id' => Dealer::class, 'invoice_id' => Invoice::class] as $key => $model) {
            $ids = array_unique(array_filter([$this->$key, $this->getOriginal($key)]));
            $model::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        }
    }

    protected function validateCollection(): void
    {
        if (! in_array($this->payment_type, ['customer', 'dealer'], true)
            || ! in_array($this->collection_status, array_keys(self::collectionStatusLabels()), true)
            || ! in_array($this->payment_method, array_keys(self::methodLabels()), true)
            || (float) $this->amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Enter a positive amount and valid payment type, method and status.']);
        }
        $partyKey = $this->payment_type === 'customer' ? 'customer_id' : 'dealer_id';
        $partyClass = $this->payment_type === 'customer' ? Customer::class : Dealer::class;
        if (! $partyClass::whereKey($this->$partyKey)->exists()) {
            throw ValidationException::withMessages([$partyKey => 'Select a valid customer or dealer.']);
        }
        $this->{$partyKey === 'customer_id' ? 'dealer_id' : 'customer_id'} = null;
        if ($this->collection_source === 'delivery_staff' && ! $this->collected_from_staff_id) {
            $this->collected_from_staff_id = $this->getOriginal('received_by') ?? $this->received_by;
        }
        foreach (['invoice_id' => Invoice::class, 'order_id' => Order::class] as $key => $model) {
            if ($this->$key) {
                $related = $model::find($this->$key);
                $typeKey = $key === 'invoice_id' ? 'invoice_type' : 'order_type';
                if (! $related || $related->$typeKey !== $this->payment_type || (int) $related->$partyKey !== (int) $this->$partyKey) {
                    throw ValidationException::withMessages([$key => 'The selected record does not belong to this customer/dealer.']);
                }
            }
        }
        if ($this->delivery_id) {
            $delivery = Delivery::with('order')->find($this->delivery_id);
            if (! $delivery?->order || (int) $delivery->order->$partyKey !== (int) $this->$partyKey
                || ($this->order_id && (int) $delivery->order_id !== (int) $this->order_id)) {
                throw ValidationException::withMessages(['delivery_id' => 'The delivery must belong to the selected order and customer/dealer.']);
            }
        }
        if ($this->invoice_id && $this->collection_status === 'accepted' && ! $this->trashed()
            && (! $this->exists || $this->isDirty(['amount', 'invoice_id', 'collection_status', 'deleted_at']))) {
            $invoice = Invoice::findOrFail($this->invoice_id);
            $paid = $invoice->payments()->accepted()->when($this->exists, fn ($q) => $q->where('id', '<>', $this->id))->sum('amount');
            if ($invoice->invoice_status === 'cancelled' || round((float) $paid + (float) $this->amount, 2) > (float) $invoice->total_amount) {
                throw ValidationException::withMessages(['amount' => 'The payment exceeds the available invoice balance or the invoice is cancelled.']);
            }
        }
    }

    public function accountTransaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AccountTransaction::class);
    }

    public static function generatePaymentNo(null|string|Carbon $date = null): string
    {
        $sequenceDate = $date instanceof Carbon
            ? $date->toDateString()
            : Carbon::parse($date ?? today())->toDateString();

        $nextNumber = DB::transaction(function () use ($sequenceDate): int {
            PaymentNumberSequence::query()->insertOrIgnore([
                'sequence_date' => $sequenceDate,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = PaymentNumberSequence::query()
                ->whereDate('sequence_date', $sequenceDate)
                ->lockForUpdate()
                ->firstOrFail();

            $sequence->increment('last_number');

            return $sequence->last_number;
        });

        return 'WF-PAY-' . Carbon::parse($sequenceDate)->format('ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
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

    public function collectedFromStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_from_staff_id');
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
