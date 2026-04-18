<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function index()
    {
        $customer = auth()->user()->customer;

        $payments = $customer->payments()
            ->with('invoice')
            ->latest('payment_date')
            ->paginate(15);

        return view('customer.payments.index', compact('customer', 'payments'));
    }
}
