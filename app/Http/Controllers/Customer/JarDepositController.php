<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class JarDepositController extends Controller
{
    public function index()
    {
        $customer = auth()->user()->customer;

        $deposits = $customer->jarDeposits()
            ->with('product')
            ->latest('transaction_date')
            ->paginate(15);

        return view('customer.jar-deposits.index', compact('customer', 'deposits'));
    }
}
