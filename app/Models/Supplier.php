<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'mobile', 'address', 'opening_due', 'total_purchase',
        'total_paid', 'current_due', 'note', 'status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'opening_due' => 'decimal:2',
        'total_purchase' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'current_due' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function jarPurchases(): HasMany
    {
        return $this->hasMany(JarPurchase::class);
    }

    public function recalculateDue(): void
    {
        $this->total_purchase = $this->jarPurchases()->sum('total_amount');
        $this->total_paid = $this->jarPurchases()->sum('paid_amount');
        $this->current_due = (float) $this->opening_due + (float) $this->total_purchase - (float) $this->total_paid;
        $this->saveQuietly();
    }
}
