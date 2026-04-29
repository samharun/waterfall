<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class FirebaseNotificationService
{
    public function __construct(
        private readonly FcmService $fcmService,
    ) {}

    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $result = $this->fcmService->sendToToken(
            token: $token,
            title: $title,
            body: $body,
            data: $this->stringData($data),
        );

        return ($result['success'] ?? false) === true;
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        $tokens = $user->fcmTokens()->get();

        $result = [
            'sent' => 0,
            'failed' => 0,
            'invalidated' => 0,
        ];

        foreach ($tokens as $token) {
            try {
                $sendResult = $this->fcmService->sendToToken(
                    token: $token->fcm_token,
                    title: $title,
                    body: $body,
                    data: $this->stringData($data),
                );

                if (($sendResult['success'] ?? false) === true) {
                    $token->forceFill(['last_used_at' => now()])->save();
                    $result['sent']++;

                    continue;
                }

                $result['failed']++;

                if (($sendResult['invalid_token'] ?? false) === true) {
                    $token->delete();
                    $result['invalidated']++;
                }

                Log::warning('Failed to send delivery user push notification.', [
                    'user_id' => $user->getKey(),
                    'user_fcm_token_id' => $token->getKey(),
                    'result' => $sendResult,
                ]);
            } catch (Throwable $exception) {
                $result['failed']++;

                Log::warning('Delivery user push notification exception.', [
                    'user_id' => $user->getKey(),
                    'user_fcm_token_id' => $token->getKey(),
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $result;
    }

    /**
     * @param  Collection<int, User>|array<int, User>  $users
     */
    public function sendToUsers(Collection|array $users, string $title, string $body, array $data = []): array
    {
        $result = [
            'users' => 0,
            'sent' => 0,
            'failed' => 0,
            'invalidated' => 0,
        ];

        foreach ($users as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $userResult = $this->sendToUser($user, $title, $body, $data);

            $result['users']++;
            $result['sent'] += $userResult['sent'];
            $result['failed'] += $userResult['failed'];
            $result['invalidated'] += $userResult['invalidated'];
        }

        return $result;
    }

    private function stringData(array $data): array
    {
        return collect($data)
            ->map(fn (mixed $value): string => (string) $value)
            ->all();
    }
}
