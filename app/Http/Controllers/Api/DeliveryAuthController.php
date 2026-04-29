<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeliveryAuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', 'regex:/^01[0-9]{9}$/'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $user = User::query()
            ->where('mobile', $data['mobile'])
            ->whereIn('role', ['delivery_staff', 'delivery_manager'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->errorResponse('Invalid mobile number or password.', 422);
        }

        $token = $user->createToken('waterfall_delivery_app')->plainTextToken;

        return $this->successResponse('Login successful.', [
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if (! $this->isDeliveryUser($request->user())) {
            return $this->forbiddenResponse();
        }

        try {
            $fcmToken = $request->input('fcm_token');

            if (is_string($fcmToken) && $fcmToken !== '') {
                UserFcmToken::query()
                    ->where('user_id', $request->user()->id)
                    ->where('token_hash', UserFcmToken::hashToken($fcmToken))
                    ->delete();
            }
        } catch (\Throwable $exception) {
            Log::warning('Delivery logout FCM token cleanup failed.', [
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);
        }

        $request->user()?->currentAccessToken()?->delete();

        return $this->successResponse('Logout successful.');
    }

    public function profile(Request $request): JsonResponse
    {
        if (! $this->isDeliveryUser($request->user())) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse('Profile loaded successfully.', [
            'user' => $this->userPayload($request->user()),
        ]);
    }

    private function userPayload(User $user): array
    {
        $zone = $user->role === 'delivery_manager'
            ? $user->managedZones()->orderBy('name')->first()
            : $user->assignedDeliveries()->with('zone')->latest('assigned_at')->first()?->zone;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'mobile' => $user->mobile,
            'role' => $user->role,
            'zone_name' => $zone?->name,
            'line_name' => null,
        ];
    }

    private function isDeliveryUser(mixed $user): bool
    {
        return $user instanceof User
            && in_array($user->role, ['delivery_staff', 'delivery_manager'], true);
    }
}
