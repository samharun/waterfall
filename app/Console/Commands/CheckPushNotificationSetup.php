<?php

namespace App\Console\Commands;

use App\Models\CustomerDeviceToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonException;

class CheckPushNotificationSetup extends Command
{
    protected $signature = 'waterfall:push-diagnostics';

    protected $description = 'Check Firebase, queue, and customer device-token setup for mobile push notifications.';

    public function handle(): int
    {
        $projectId = config('services.firebase.project_id');
        $credentialPath = config('services.firebase.credentials');
        $queueConnection = config('queue.default');

        $this->info('Push notification diagnostics');
        $this->line('Firebase project: '.($projectId ?: 'missing'));
        $this->line('Firebase credentials: '.($credentialPath ?: 'missing'));
        $this->line('Queue connection: '.$queueConnection);

        $hasError = false;

        if (! is_string($projectId) || $projectId === '') {
            $this->error('FIREBASE_PROJECT_ID is missing.');
            $hasError = true;
        }

        if (! is_string($credentialPath) || $credentialPath === '') {
            $this->error('FIREBASE_CREDENTIALS is missing.');
            $hasError = true;
        } elseif (! is_file($credentialPath)) {
            $this->error('Firebase credentials file was not found at the resolved path.');
            $hasError = true;
        } else {
            $hasError = ! $this->checkCredentialFile($credentialPath) || $hasError;
        }

        $this->line('Registered device tokens: '.CustomerDeviceToken::query()->count());
        $this->line('Active device tokens: '.CustomerDeviceToken::query()->where('is_active', true)->count());

        if ($queueConnection === 'database') {
            $this->line('Pending notification jobs: '.DB::table('jobs')->where('queue', 'notifications')->count());
            $this->line('Failed notification jobs: '.DB::table('failed_jobs')->where('queue', 'notifications')->count());
            $this->warn('Run queue worker: php artisan queue:listen --queue=default,notifications --tries=1 --timeout=0');
        }

        return $hasError ? self::FAILURE : self::SUCCESS;
    }

    private function checkCredentialFile(string $credentialPath): bool
    {
        try {
            $payload = json_decode((string) file_get_contents($credentialPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->error('Firebase credentials file is not valid JSON.');

            return false;
        }

        if (! is_array($payload)) {
            $this->error('Firebase credentials file is not a JSON object.');

            return false;
        }

        if (isset($payload['project_info']) && isset($payload['client']) && ! isset($payload['private_key'])) {
            $this->error('This is google-services.json. Backend FCM needs a Firebase service-account JSON file.');

            return false;
        }

        $missing = collect(['type', 'project_id', 'client_email', 'private_key'])
            ->reject(fn (string $key): bool => isset($payload[$key]) && is_string($payload[$key]) && trim($payload[$key]) !== '')
            ->values();

        if ($missing->isNotEmpty()) {
            $this->error('Firebase service-account JSON is missing: '.$missing->implode(', '));

            return false;
        }

        if ($payload['type'] !== 'service_account') {
            $this->error('Firebase credentials file must have type "service_account".');

            return false;
        }

        $this->info('Firebase service-account JSON looks valid.');

        return true;
    }
}
