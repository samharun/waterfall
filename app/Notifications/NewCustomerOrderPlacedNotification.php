<?php

namespace App\Notifications;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class NewCustomerOrderPlacedNotification extends Notification
{
    public function __construct(
        protected Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->order->loadMissing(['customer', 'zone']);

        $customerName = $this->order->customer?->name ?? 'Customer';
        $zoneName = $this->order->zone?->name ?? 'Unassigned zone';
        $amount = number_format((float) $this->order->total_amount, 2);

        return FilamentNotification::make()
            ->title("New order {$this->order->order_no}")
            ->body("{$customerName} placed a customer order for Tk {$amount} in {$zoneName}.")
            ->icon('heroicon-o-shopping-cart')
            ->iconColor('warning')
            ->actions([
                Action::make('viewOrder')
                    ->label('View order')
                    ->button()
                    ->url(OrderResource::getUrl('edit', ['record' => $this->order]), shouldOpenInNewTab: true),
            ])
            ->getDatabaseMessage();
    }
}
