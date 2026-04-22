<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerDeviceToken;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Services\FcmService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class TestOrderStatusPushNotification extends Command
{
    protected $signature = 'waterfall:test-order-status-push
                            {--order-id= : Existing order ID to use in the payload}
                            {--customer-id= : Customer ID to use when order-id is not given}
                            {--token= : Send to a single device token instead of active tokens for the customer}
                            {--status=confirmed : Status value to include in the payload}
                            {--title= : Custom notification title}
                            {--body= : Custom notification body}
                            {--dry-run : Preview the payload and targets without sending}';

    protected $description = 'Send a real FCM order-status test push to a customer device token or all active tokens for a customer.';

    public function __construct(
        private readonly FcmService $fcmService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $order = $this->resolveOrder();
        $customer = $order?->customer ?? $this->resolveCustomer();
        $directToken = $this->option('token');

        if (! $order) {
            $this->error('An order context is required. Pass --order-id or use --customer-id for a customer that already has an order.');

            return self::FAILURE;
        }

        if (! $customer) {
            $this->error('Could not resolve a customer for this test push.');

            return self::FAILURE;
        }

        $tokens = $this->resolveTokens($customer, $directToken);

        if ($tokens->isEmpty()) {
            $this->warn('No target device tokens found.');

            return self::FAILURE;
        }

        $status = (string) $this->option('status');
        $title = $this->option('title') ?: sprintf('Order %s updated', $order->order_no ?? ('#'.$order->getKey()));
        $body = $this->option('body') ?: sprintf(
            'Test push: your order status is now %s.',
            str($status)->replace('_', ' ')->title(),
        );
        $data = [
            'type' => 'order_status',
            'order_id' => (string) $order->getKey(),
            'status' => $status,
        ];

        $this->info(sprintf('Customer: %s (%s)', $customer->name, $customer->customer_id));
        $this->line(sprintf('Order: %s', $order->order_no ?? $order->getKey()));
        $this->line(sprintf('Targets: %d', $tokens->count()));
        $this->line(sprintf('Payload: %s', json_encode([
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ], JSON_UNESCAPED_SLASHES)));

        if ($this->option('dry-run')) {
            $this->comment('Dry run only. Nothing was sent.');

            return self::SUCCESS;
        }

        $successCount = 0;

        foreach ($tokens as $tokenRow) {
            $deviceToken = $tokenRow instanceof CustomerDeviceToken
                ? $tokenRow->device_token
                : (string) $tokenRow;

            $result = $this->fcmService->sendToToken(
                token: $deviceToken,
                title: $title,
                body: $body,
                data: $data,
            );

            if ($result['success'] === true) {
                $successCount++;
                $this->info(sprintf('Sent successfully to %s', $this->shortToken($deviceToken)));

                if ($tokenRow instanceof CustomerDeviceToken) {
                    $tokenRow->forceFill([
                        'last_seen_at' => now(),
                        'is_active' => true,
                    ])->save();
                }

                continue;
            }

            $this->warn(sprintf(
                'Failed for %s: %s',
                $this->shortToken($deviceToken),
                $result['error'] ?? 'Unknown error',
            ));

            if (($result['invalid_token'] ?? false) === true && $tokenRow instanceof CustomerDeviceToken) {
                $tokenRow->forceFill([
                    'is_active' => false,
                    'last_seen_at' => now(),
                ])->save();
            }
        }

        if ($successCount > 0) {
            CustomerNotification::query()->create([
                'customer_id' => $customer->getKey(),
                'order_id' => $order->getKey(),
                'type' => 'order_status',
                'title' => $title,
                'body' => $body,
                'data_json' => $data,
            ]);
        }

        $this->info(sprintf('Done. Successful sends: %d / %d', $successCount, $tokens->count()));

        return $successCount > 0 ? self::SUCCESS : self::FAILURE;
    }

    private function resolveOrder(): ?Order
    {
        $orderId = $this->option('order-id');

        if ($orderId) {
            return Order::with('customer')->find((int) $orderId);
        }

        $customer = $this->resolveCustomer();

        return $customer?->orders()->latest('id')->with('customer')->first();
    }

    private function resolveCustomer(): ?Customer
    {
        $customerId = $this->option('customer-id');

        if (! $customerId) {
            return null;
        }

        return Customer::find((int) $customerId);
    }

    /**
     * @return Collection<int, CustomerDeviceToken|string>
     */
    private function resolveTokens(Customer $customer, ?string $directToken): Collection
    {
        if (is_string($directToken) && $directToken !== '') {
            return collect([$directToken]);
        }

        return $customer->deviceTokens()
            ->active()
            ->get();
    }

    private function shortToken(string $token): string
    {
        if (strlen($token) <= 16) {
            return $token;
        }

        return substr($token, 0, 8).'...'.substr($token, -8);
    }
}
