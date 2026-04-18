<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    private function getCustomer()
    {
        return Auth::user()->customer;
    }

    private function getActiveSubscription()
    {
        return $this->getCustomer()
            ->subscriptions()
            ->whereIn('status', ['active', 'paused'])
            ->latest()
            ->first();
    }

    public function show()
    {
        $customer     = $this->getCustomer();
        $subscription = $this->getActiveSubscription();

        return view('customer.subscription.show', compact('customer', 'subscription'));
    }

    public function create()
    {
        // Redirect if already has active/paused subscription
        if ($this->getActiveSubscription()) {
            return redirect()->route('customer.subscription.show')
                ->with('info', 'You already have an active subscription.');
        }

        $products = Product::active()->orderBy('name')->get();
        $customer = $this->getCustomer();

        return view('customer.subscription.create', compact('products', 'customer'));
    }

    public function store(Request $request)
    {
        $customer = $this->getCustomer();

        if ($this->getActiveSubscription()) {
            return redirect()->route('customer.subscription.show');
        }

        $validated = $request->validate([
            'product_id'              => ['required', 'exists:products,id'],
            'quantity'                => ['required', 'integer', 'min:1'],
            'frequency'               => ['required', 'in:daily,weekly,custom_days,monthly'],
            'delivery_days'           => ['required_if:frequency,weekly', 'required_if:frequency,custom_days', 'nullable', 'array'],
            'delivery_days.*'         => ['in:saturday,sunday,monday,tuesday,wednesday,thursday,friday'],
            'preferred_delivery_slot' => ['required', 'in:morning,afternoon,evening,custom'],
            'preferred_delivery_time' => ['required_if:preferred_delivery_slot,custom', 'nullable'],
            'start_date'              => ['required', 'date'],
            'remarks'                 => ['nullable', 'string', 'max:500'],
        ]);

        $sub = CustomerSubscription::create([
            ...$validated,
            'subscription_no' => CustomerSubscription::generateSubscriptionNo(),
            'customer_id'     => $customer->id,
            'status'          => 'active',
            'created_by'      => Auth::id(),
            'updated_by'      => Auth::id(),
        ]);

        // Calculate next delivery date
        $next = $sub->calculateNextDeliveryDate();
        $sub->update(['next_delivery_date' => $next?->toDateString()]);

        return redirect()->route('customer.subscription.show')
            ->with('success', 'Subscription created successfully.');
    }

    public function edit()
    {
        $customer     = $this->getCustomer();
        $subscription = $this->getActiveSubscription();

        if (! $subscription) {
            return redirect()->route('customer.subscription.show');
        }

        $products = Product::active()->orderBy('name')->get();

        return view('customer.subscription.edit', compact('customer', 'subscription', 'products'));
    }

    public function update(Request $request)
    {
        $customer     = $this->getCustomer();
        $subscription = $this->getActiveSubscription();

        if (! $subscription) {
            return redirect()->route('customer.subscription.show');
        }

        $validated = $request->validate([
            'product_id'              => ['required', 'exists:products,id'],
            'quantity'                => ['required', 'integer', 'min:1'],
            'frequency'               => ['required', 'in:daily,weekly,custom_days,monthly'],
            'delivery_days'           => ['nullable', 'array'],
            'delivery_days.*'         => ['in:saturday,sunday,monday,tuesday,wednesday,thursday,friday'],
            'preferred_delivery_slot' => ['required', 'in:morning,afternoon,evening,custom'],
            'preferred_delivery_time' => ['required_if:preferred_delivery_slot,custom', 'nullable'],
            'start_date'              => ['required', 'date'],
            'remarks'                 => ['nullable', 'string', 'max:500'],
        ]);

        $subscription->update([...$validated, 'updated_by' => Auth::id()]);

        $next = $subscription->calculateNextDeliveryDate();
        $subscription->update(['next_delivery_date' => $next?->toDateString()]);

        return redirect()->route('customer.subscription.show')
            ->with('success', 'Subscription updated successfully.');
    }

    public function pause(Request $request)
    {
        $subscription = $this->getActiveSubscription();

        if (! $subscription || ! $subscription->isActive()) {
            return back()->withErrors(['error' => 'No active subscription to pause.']);
        }

        $request->validate([
            'paused_from'  => ['required', 'date'],
            'paused_to'    => ['nullable', 'date', 'after_or_equal:paused_from'],
            'pause_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $subscription->pause(
            $request->pause_reason,
            $request->paused_from,
            $request->paused_to
        );

        return redirect()->route('customer.subscription.show')
            ->with('success', 'Subscription paused successfully.');
    }

    public function resume()
    {
        $subscription = $this->getActiveSubscription();

        if (! $subscription || ! $subscription->isPaused()) {
            return back()->withErrors(['error' => 'No paused subscription to resume.']);
        }

        $subscription->resume();

        return redirect()->route('customer.subscription.show')
            ->with('success', 'Subscription resumed successfully.');
    }

    public function cancel(Request $request)
    {
        $subscription = $this->getActiveSubscription();

        if (! $subscription) {
            return back()->withErrors(['error' => 'No active subscription to cancel.']);
        }

        $subscription->cancel();

        return redirect()->route('customer.subscription.show')
            ->with('success', 'Subscription cancelled.');
    }
}
