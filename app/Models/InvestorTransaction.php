<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvestorTransaction extends Model
{
    use SoftDeletes { restore as protected restoreWithoutAccounting; }
    use \App\Models\Concerns\HasAccountingLifecycle;

    public function restore()
    {
        return $this->restoreWithAccounting();
    }

    protected $fillable = [
        'investor_id', 'transaction_date', 'transaction_type', 'amount',
        'payment_account_id', 'account_transaction_id', 'reference_no', 'note',
        'attachment', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function accountTransaction(): BelongsTo
    {
        return $this->belongsTo(AccountTransaction::class);
    }

    public static function typeLabels(): array
    {
        return [
            'investment_received' => 'Investment Received',
            'return_paid' => 'Return Paid',
            'loan_repayment' => 'Loan Repayment',
        ];
    }
}
