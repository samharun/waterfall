<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'mobile', 'address', 'investment_type', 'total_invested',
        'total_returned', 'current_balance', 'note', 'status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'total_invested' => 'decimal:2',
        'total_returned' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(InvestorTransaction::class);
    }

    public function recalculateBalance(): void
    {
        $this->total_invested = $this->transactions()->where('transaction_type', 'investment_received')->sum('amount');
        $this->total_returned = $this->transactions()->whereIn('transaction_type', ['return_paid', 'loan_repayment'])->sum('amount');
        $this->current_balance = (float) $this->total_invested - (float) $this->total_returned;
        $this->saveQuietly();
    }

    public static function typeLabels(): array
    {
        return ['capital' => 'Capital', 'loan' => 'Loan', 'partner_contribution' => 'Partner Contribution'];
    }
}
