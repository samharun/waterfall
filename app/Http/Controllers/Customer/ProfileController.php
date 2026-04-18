<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        $customer = auth()->user()->customer->load('zone');

        return view('customer.profile', compact('customer'));
    }

    public function edit()
    {
        $customer = auth()->user()->customer->load('zone');
        $zones    = Zone::active()->orderBy('name')->get();

        return view('customer.profile-edit', compact('customer', 'zones'));
    }

    public function update(Request $request)
    {
        $customer = auth()->user()->customer;

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'name_bn'               => ['nullable', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'address'               => ['required', 'string', 'max:500'],
            'address_bn'            => ['nullable', 'string', 'max:500'],
            'zone_id'               => ['nullable', 'exists:zones,id'],
            'customer_type'         => ['required', 'in:residential,corporate'],
            'default_delivery_slot' => ['nullable', 'in:now,morning,afternoon,evening,custom'],
        ]);

        $customer->update($validated);

        // Sync name on linked user account
        $customer->user?->update(['name' => $validated['name']]);

        return redirect()->route('customer.profile')
            ->with('success', __('customer.profile_updated'));
    }
}
