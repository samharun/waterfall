<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'type', 'account_no', 'opening_balance', 'current_balance',
        'status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (PaymentAccount $account) {
            if ($account->current_balance === null) {
                $account->current_balance = $account->opening_balance ?? 0;
            }
        });
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public static function typeLabels(): array
    {
        return [
            'cash' => 'Cash',
            'bank' => 'Bank',
            'mobile_banking' => 'Mobile Banking',
            'card' => 'Card',
            'other' => 'Other',
        ];
    }
}
