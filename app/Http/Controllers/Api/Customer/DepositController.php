<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Customer\Concerns\ApiResponse;
use App\Models\JarDeposit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        $transactions = JarDeposit::forCustomer($customer->id)
            ->with('product')
            ->orderByDesc('transaction_date')
            ->paginate(20);

        // Aggregate totals
        $all = JarDeposit::forCustomer($customer->id)->get();

        $totalDeposit  = $all->where('transaction_type', 'deposit_received')->sum('deposit_amount');
        $jarsIssued    = $all->where('transaction_type', 'jar_issued')->sum('quantity');
        $jarsReturned  = $all->where('transaction_type', 'jar_returned')->sum('quantity');
        $jarBalance    = $customer->jar_deposit_qty ?? 0;

        $items = $transactions->getCollection()->map(fn (JarDeposit $jd) => [
            'id'               => $jd->id,
            'deposit_no'       => $jd->deposit_no,
            'transaction_type' => $jd->transaction_type,
            'product'          => $jd->product?->name,
            'quantity'         => $jd->quantity,
            'deposit_amount'   => (float) $jd->deposit_amount,
            'transaction_date' => $jd->transaction_date?->toDateString(),
            'remarks'          => $jd->remarks,
        ]);

        return $this->success('Deposits retrieved.', [
            'summary' => [
                'total_deposit_amount' => (float) $totalDeposit,
                'jars_issued'          => (int) $jarsIssued,
                'jars_returned'        => (int) $jarsReturned,
                'current_jar_balance'  => (int) $jarBalance,
            ],
            'transactions' => $items,
            'pagination'   => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }
}
