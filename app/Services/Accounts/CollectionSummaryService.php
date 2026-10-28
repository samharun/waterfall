<?php

namespace App\Services\Accounts;

use App\Models\Payment;

class CollectionSummaryService
{
    public function totals(): array
    {
        $today = today();

        return [
            'today' => (float) Payment::accepted()->whereDate('payment_date', $today)->sum('amount'),
            'month' => (float) Payment::accepted()->whereBetween('payment_date', [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()])->sum('amount'),
            'all_time' => (float) Payment::accepted()->sum('amount'),
        ];
    }
}
