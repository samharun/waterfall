<?php

namespace App\Notifications;

use App\Models\Dealer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DealerApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public Dealer $dealer) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Waterfall Dealer Account Has Been Approved!')
            ->greeting("Hello {$this->dealer->name},")
            ->line('Your Waterfall dealer account has been approved.')
            ->line("Your Dealer Code: **{$this->dealer->dealer_code}**")
            ->action('Login to Dealer Portal', url('/dealer/login'))
            ->line('You can now place bulk orders and manage your account.')
            ->salutation('— Waterfall Team');
    }
}
