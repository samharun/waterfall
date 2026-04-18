<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $partyType, // 'customer' or 'dealer'
        public string $name,
        public string $mobile,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = ucfirst($this->partyType);
        $url   = $this->partyType === 'dealer'
            ? url('/admin/dealers')
            : url('/admin/customers');

        return (new MailMessage)
            ->subject("New {$label} Registration Pending Approval")
            ->greeting("Hello Admin,")
            ->line("A new {$label} has registered and is awaiting approval.")
            ->line("Name: **{$this->name}**")
            ->line("Mobile: **{$this->mobile}**")
            ->action("Review in Admin Panel", $url)
            ->salutation('— Waterfall System');
    }
}
