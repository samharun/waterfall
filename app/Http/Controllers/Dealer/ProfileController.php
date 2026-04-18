<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        $dealer = auth()->user()->dealer->load('zone');

        return view('dealer.profile', compact('dealer'));
    }
}
