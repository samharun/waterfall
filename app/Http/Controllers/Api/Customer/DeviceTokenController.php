<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\Customer\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\DeleteDeviceTokenRequest;
use App\Http\Requests\Api\Customer\StoreDeviceTokenRequest;
use App\Models\CustomerDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeviceTokenController extends Controller
{
    use ApiResponse;

    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $customer = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($customer, $validated): void {
            CustomerDeviceToken::query()->updateOrCreate(
                ['device_token' => $validated['device_token']],
                [
                    'customer_id' => $customer->getKey(),
                    'platform' => $validated['platform'] ?? null,
                    'device_name' => $validated['device_name'] ?? null,
                    'app_version' => $validated['app_version'] ?? null,
                    'last_seen_at' => now(),
                    'is_active' => true,
                ],
            );
        });

        return $this->success('Device token registered.');
    }

    public function destroy(DeleteDeviceTokenRequest $request): JsonResponse
    {
        $customer = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($customer, $validated): void {
            CustomerDeviceToken::query()
                ->where('customer_id', $customer->getKey())
                ->where('device_token', $validated['device_token'])
                ->update([
                    'is_active' => false,
                    'last_seen_at' => now(),
                ]);
        });

        return $this->success('Device token removed.');
    }
}
