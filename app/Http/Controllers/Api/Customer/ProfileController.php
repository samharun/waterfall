<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Customer\Concerns\ApiResponse;
use App\Models\CustomerPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    use ApiResponse;

    // ── GET /api/customer/profile ──────────────────────────────────

    public function profile(Request $request): JsonResponse
    {
        $locale   = $this->resolveLocale($request);
        $customer = $request->user()->load('zone');

        $customPriceCount = CustomerPrice::where('customer_id', $customer->id)
            ->currentlyEffective()
            ->count();

        return $this->success('Profile retrieved.', [
            'id'              => $customer->id,
            'customer_id'     => $customer->customer_id,

            // Both name variants + locale-resolved display value
            'name'            => $customer->name,
            'name_bn'         => $customer->name_bn,
            'display_name'    => $this->localized($customer->name_bn, $customer->name, $locale),

            'mobile'          => $customer->mobile,
            'email'           => $customer->email,

            // Both address variants + locale-resolved display value
            'address'         => $customer->address,
            'address_bn'      => $customer->address_bn,
            'display_address' => $this->localized($customer->address_bn, $customer->address, $locale),

            // Customer type — raw key + localized label
            'customer_type'       => $customer->customer_type,
            'customer_type_label' => $this->translateType($customer->customer_type, $locale),

            // Approval status — raw key + localized label
            'status'              => $customer->approval_status,
            'status_label'        => $this->translateStatus($customer->approval_status, $locale),

            'zone' => $customer->zone ? [
                'id'   => $customer->zone->id,
                'name' => $customer->zone->name,
                'code' => $customer->zone->code,
            ] : null,

            'default_delivery_slot'       => $customer->default_delivery_slot,
            'default_delivery_slot_label' => $this->translateSlot($customer->default_delivery_slot, $locale),

            'has_custom_pricing' => $customPriceCount > 0,
            'member_since'       => $customer->created_at?->toDateString(),
        ]);
    }

    // ── PUT /api/customer/profile ──────────────────────────────────

    public function update(Request $request): JsonResponse
    {
        $customer = $request->user();

        try {
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
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $customer->update($validated);
        $customer->user?->update(['name' => $validated['name']]);

        $locale = $this->resolveLocale($request);

        return $this->success('Profile updated successfully.', [
            'id'              => $customer->id,
            'customer_id'     => $customer->customer_id,
            'name'            => $customer->name,
            'name_bn'         => $customer->name_bn,
            'display_name'    => $this->localized($customer->name_bn, $customer->name, $locale),
            'address'         => $customer->address,
            'address_bn'      => $customer->address_bn,
            'display_address' => $this->localized($customer->address_bn, $customer->address, $locale),
            'customer_type'       => $customer->customer_type,
            'customer_type_label' => $this->translateType($customer->customer_type, $locale),
        ]);
    }
}
