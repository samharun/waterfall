<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
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

            $this->assertServiceAccountCredentials($credentialPath);

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
                                    'channel_id' => 'waterfall_delivery_channel',
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

    private function assertServiceAccountCredentials(string $credentialPath): void
    {
        try {
            $payload = json_decode((string) file_get_contents($credentialPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \RuntimeException('Firebase credentials file is not valid JSON.', previous: $exception);
        }

        if (! is_array($payload)) {
            throw new \RuntimeException('Firebase credentials file is not a valid JSON object.');
        }

        if (isset($payload['project_info']) && isset($payload['client']) && ! isset($payload['private_key'])) {
            throw new \RuntimeException(
                'Firebase credentials must be a service-account JSON file, not the mobile google-services.json file.'
            );
        }

        $requiredKeys = ['type', 'project_id', 'client_email', 'private_key'];

        foreach ($requiredKeys as $requiredKey) {
            if (! isset($payload[$requiredKey]) || ! is_string($payload[$requiredKey]) || trim($payload[$requiredKey]) === '') {
                throw new \RuntimeException(
                    'Firebase credentials must be a service-account JSON file with type, project_id, client_email, and private_key.'
                );
            }
        }

        if ($payload['type'] !== 'service_account') {
            throw new \RuntimeException('Firebase credentials file must have type "service_account".');
        }
    }
}
