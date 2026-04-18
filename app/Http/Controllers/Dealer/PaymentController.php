<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function index()
    {
        $dealer = auth()->user()->dealer;

        $payments = $dealer->payments()
            ->with('invoice')
            ->latest('payment_date')
            ->paginate(15);

        return view('dealer.payments.index', compact('dealer', 'payments'));
    }
}
