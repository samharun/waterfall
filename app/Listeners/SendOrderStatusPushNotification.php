<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\CustomerDeviceToken;
use App\Models\CustomerNotification;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderStatusPushNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public bool $afterCommit = true;

    public int $tries = 3;

    public function __construct(
        private readonly FcmService $fcmService,
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order->loadMissing('customer');
        $customer = $order->customer;

        if (! $customer) {
            return;
        }

        $deviceTokens = CustomerDeviceToken::query()
            ->active()
            ->where('customer_id', $customer->getKey())
            ->get();

        if ($deviceTokens->isEmpty()) {
            return;
        }

        $title = sprintf('Order %s updated', $order->order_no ?? ('#'.$order->getKey()));
        $body = sprintf(
            'Your order status changed from %s to %s.',
            str($event->oldStatus)->replace('_', ' ')->title(),
            str($event->newStatus)->replace('_', ' ')->title(),
        );
        $data = [
            'type' => 'order_status',
            'order_id' => (string) $order->getKey(),
            'status' => (string) $event->newStatus,
        ];

        CustomerNotification::query()->create([
            'customer_id' => $customer->getKey(),
            'order_id' => $order->getKey(),
            'type' => 'order_status',
            'title' => $title,
            'body' => $body,
            'data_json' => $data,
        ]);

        foreach ($deviceTokens as $deviceToken) {
            $result = $this->fcmService->sendToToken(
                token: $deviceToken->device_token,
                title: $title,
                body: $body,
                data: $data,
            );

            if ($result['success'] === true) {
                $deviceToken->forceFill([
                    'last_seen_at' => now(),
                    'is_active' => true,
                ])->save();

                continue;
            }

            if (($result['invalid_token'] ?? false) === true) {
                $deviceToken->forceFill([
                    'is_active' => false,
                    'last_seen_at' => now(),
                ])->save();
            }

            Log::warning('Failed to send customer order status push.', [
                'order_id' => $order->getKey(),
                'customer_id' => $customer->getKey(),
                'device_token_id' => $deviceToken->getKey(),
                'result' => $result,
            ]);
        }
    }
}
