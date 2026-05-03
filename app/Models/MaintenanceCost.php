<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceCost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'maintenance_date', 'business_asset_id', 'maintenance_type', 'description',
        'cost', 'paid_to', 'payment_account_id', 'account_transaction_id',
        'next_service_date', 'attachment', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'cost' => 'decimal:2',
        'next_service_date' => 'date',
    ];

    public function businessAsset(): BelongsTo
    {
        return $this->belongsTo(BusinessAsset::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function accountTransaction(): BelongsTo
    {
        return $this->belongsTo(AccountTransaction::class);
    }
}
