<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public Customer $customer) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Waterfall Account Has Been Approved!')
            ->greeting("Hello {$this->customer->name},")
            ->line('Great news! Your Waterfall customer account has been approved.')
            ->line("Your Customer ID: **{$this->customer->customer_id}**")
            ->action('Login to Your Account', url('/customer/login'))
            ->line('You can now place water jar orders through our portal.')
            ->salutation('— Waterfall Team');
    }
}
