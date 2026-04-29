<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendFirebaseNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<int, int>  $userIds
     */
    public function __construct(
        private readonly array $userIds,
        private readonly string $title,
        private readonly string $body,
        private readonly array $data = [],
    ) {
        $this->onQueue('notifications');
    }

    public function handle(FirebaseNotificationService $notificationService): void
    {
        try {
            $users = User::query()
                ->whereIn('id', $this->userIds)
                ->with('fcmTokens')
                ->get();

            $notificationService->sendToUsers($users, $this->title, $this->body, $this->data);
        } catch (Throwable $exception) {
            Log::warning('Queued Firebase notification failed.', [
                'user_ids' => $this->userIds,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
