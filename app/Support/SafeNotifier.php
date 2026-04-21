<?php

namespace App\Support;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SafeNotifier
{
    public static function send(object|null $notifiable, Notification $notification, array $context = []): bool
    {
        if (! $notifiable) {
            return false;
        }

        try {
            $notifiable->notify($notification);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Notification delivery failed.', array_merge($context, [
                'notification' => $notification::class,
                'notifiable_type' => $notifiable::class,
                'notifiable_id' => $notifiable->id ?? null,
                'error' => $e->getMessage(),
            ]));

            return false;
        }
    }
}
