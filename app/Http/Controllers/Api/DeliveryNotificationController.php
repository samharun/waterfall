<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeliveryNotificationController extends Controller
{
    use ApiResponse;

    public function saveFcmToken(Request $request): JsonResponse
    {
        if (! $this->isDeliveryUser($request->user())) {
            return $this->forbiddenResponse();
        }

        $validator = Validator::make($request->all(), [
            'fcm_token' => ['required', 'string', 'max:1000'],
            'platform' => ['nullable', Rule::in(['android', 'ios', 'web'])],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $tokenHash = UserFcmToken::hashToken($data['fcm_token']);

        DB::transaction(function () use ($request, $data, $tokenHash): void {
            UserFcmToken::query()->updateOrCreate(
                ['token_hash' => $tokenHash],
                [
                    'user_id' => $request->user()->id,
                    'fcm_token' => $data['fcm_token'],
                    'platform' => $data['platform'] ?? null,
                    'device_name' => $data['device_name'] ?? null,
                    'last_used_at' => now(),
                ],
            );
        });

        return $this->successResponse('FCM token saved successfully.');
    }

    public function deleteFcmToken(Request $request): JsonResponse
    {
        if (! $this->isDeliveryUser($request->user())) {
            return $this->forbiddenResponse();
        }

        $validator = Validator::make($request->all(), [
            'fcm_token' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $token = $validator->validated()['fcm_token'] ?? null;

        DB::transaction(function () use ($request, $token): void {
            $query = UserFcmToken::query()->where('user_id', $request->user()->id);

            if (is_string($token) && $token !== '') {
                $query->where('token_hash', UserFcmToken::hashToken($token));
            }

            $query->delete();
        });

        return $this->successResponse('FCM token deleted successfully.');
    }

    private function isDeliveryUser(mixed $user): bool
    {
        return $user instanceof User
            && in_array($user->role, ['delivery_staff', 'delivery_manager'], true);
    }
}
