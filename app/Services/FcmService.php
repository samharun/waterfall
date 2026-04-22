<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FcmService
{
    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = [],
    ): array {
        try {
            $projectId = (string) config('services.firebase.project_id');
            $credentialPath = config('services.firebase.credentials');

            if ($projectId === '' || ! is_string($credentialPath) || $credentialPath === '' || ! is_file($credentialPath)) {
                Log::error('FCM credentials are missing or invalid.', [
                    'project_id_present' => $projectId !== '',
                    'credential_path' => $credentialPath,
                ]);

                return [
                    'success' => false,
                    'invalid_token' => false,
                    'status' => null,
                    'error' => 'FCM configuration missing.',
                ];
            }

            $accessToken = $this->accessToken($credentialPath);
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->post(
                    sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $projectId),
                    [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => collect($data)
                                ->map(fn (mixed $value): string => (string) $value)
                                ->all(),
                            'android' => [
                                'priority' => 'high',
                                'notification' => [
                                    'channel_id' => 'order_status_updates',
                                ],
                            ],
                            'apns' => [
                                'headers' => [
                                    'apns-priority' => '10',
                                ],
                                'payload' => [
                                    'aps' => [
                                        'sound' => 'default',
                                    ],
                                ],
                            ],
                        ],
                    ],
                );

            if ($response->successful()) {
                return [
                    'success' => true,
                    'invalid_token' => false,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ];
            }

            $payload = $response->json();
            $errorCode = data_get($payload, 'error.details.0.errorCode');

            Log::warning('FCM request failed.', [
                'status' => $response->status(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'invalid_token' => in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'], true),
                'status' => $response->status(),
                'error' => data_get($payload, 'error.message', 'FCM request failed.'),
                'error_code' => $errorCode,
                'response' => $payload,
            ];
        } catch (Throwable $exception) {
            Log::error('FCM send exception.', [
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'invalid_token' => false,
                'status' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function accessToken(string $credentialPath): string
    {
        return Cache::remember('firebase_http_v1_access_token', now()->addMinutes(50), function () use ($credentialPath): string {
            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase.messaging'],
                $credentialPath,
            );

            $tokenData = $credentials->fetchAuthToken();
            $accessToken = $tokenData['access_token'] ?? null;

            if (! is_string($accessToken) || $accessToken === '') {
                throw new \RuntimeException('Unable to fetch Firebase access token.');
            }

            return $accessToken;
        });
    }
}
