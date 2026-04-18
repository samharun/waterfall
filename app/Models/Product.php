<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'product_type',
        'default_price',
        'deposit_amount',
        'stock_alert_qty',
        'current_stock',
        'status',
    ];

    protected $casts = [
        'default_price'   => 'decimal:2',
        'deposit_amount'  => 'decimal:2',
        'stock_alert_qty' => 'integer',
        'current_stock'   => 'integer',
    ];

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function customerPrices(): HasMany
    {
        return $this->hasMany(CustomerPrice::class);
    }

    public function dealerPrices(): HasMany
    {
        return $this->hasMany(DealerPrice::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function jarDeposits(): HasMany
    {
        return $this->hasMany(JarDeposit::class);
    }

    public function customerSubscriptions(): HasMany
    {
        return $this->hasMany(CustomerSubscription::class);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public static function typeLabels(): array
    {
        return [
            'jar'       => 'Jar',
            'bottle'    => 'Bottle',
            'accessory' => 'Accessory',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'active'   => 'Active',
            'inactive' => 'Inactive',
        ];
    }

    // ── Pricing helpers ────────────────────────────────────────────

    /**
     * Return the effective price for a specific customer on a given date.
     * Falls back to default_price if no active custom price is found.
     */
    public function getPriceForCustomer(int $customerId, ?string $date = null): string
    {
        $price = $this->customerPrices()
            ->currentlyEffective($date)
            ->where('customer_id', $customerId)
            ->orderByDesc('effective_from')
            ->value('custom_price');

        return $price ?? $this->default_price;
    }

    /**
     * Return the effective price for a specific dealer on a given date.
     * Falls back to default_price if no active custom price is found.
     */
    public function getPriceForDealer(int $dealerId, ?string $date = null): string
    {
        $price = $this->dealerPrices()
            ->currentlyEffective($date)
            ->where('dealer_id', $dealerId)
            ->orderByDesc('effective_from')
            ->value('custom_price');

        return $price ?? $this->default_price;
    }

    // ── Stock helpers ──────────────────────────────────────────────

    /**
     * Recalculate current_stock from all non-deleted stock transactions.
     */
    public function recalculateCurrentStock(): void
    {
        $stock = $this->stockTransactions()->get()->sum(
            fn (StockTransaction $tx) => $tx->signedQuantity()
        );

        $this->update(['current_stock' => max(0, $stock)]);
    }

    /**
     * Returns true when active product stock is at or below alert threshold.
     */
    public function isLowStock(): bool
    {
        return $this->status === 'active'
            && $this->current_stock <= $this->stock_alert_qty;
    }
}
