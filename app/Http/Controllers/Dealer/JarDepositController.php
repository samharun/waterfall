<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;

class JarDepositController extends Controller
{
    public function index()
    {
        $dealer = auth()->user()->dealer;

        $deposits = $dealer->jarDeposits()
            ->with('product')
            ->latest('transaction_date')
            ->paginate(15);

        // Calculate jar deposit qty from records (dealers table may not have the column)
        $jarDepositQty = isset($dealer->jar_deposit_qty)
            ? (int) $dealer->jar_deposit_qty
            : (int) $dealer->jarDeposits()->get()->sum(fn ($jd) => $jd->signedQuantity());

        return view('dealer.jar-deposits.index', compact('dealer', 'deposits', 'jarDepositQty'));
    }
}
