<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffSalaryPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'staff_id', 'staff_name', 'salary_month', 'basic_salary', 'advance_deduction',
        'bonus', 'deduction', 'net_payable', 'paid_amount', 'due_amount',
        'payment_date', 'payment_account_id', 'account_transaction_id', 'status',
        'note', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function accountTransaction(): BelongsTo
    {
        return $this->belongsTo(AccountTransaction::class);
    }

    public static function statusLabels(): array
    {
        return ['paid' => 'Paid', 'partial' => 'Partial', 'unpaid' => 'Unpaid'];
    }
}
