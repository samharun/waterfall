<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JarPurchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_date', 'supplier_id', 'supplier_name', 'jar_type', 'quantity',
        'unit_price', 'total_amount', 'paid_amount', 'due_amount',
        'payment_account_id', 'account_transaction_id', 'payment_status',
        'note', 'attachment', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
