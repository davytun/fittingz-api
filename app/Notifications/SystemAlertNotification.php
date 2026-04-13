<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $level = 'info'
    ) {}

    public function via(object $notifiable): array
    {
        // System alert emails always send regardless of email_notifications preference
        // because they target internal admin users only (config('app.admin_email')),
        // not regular end users.
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Fittingz Alert: {$this->title}")
            ->line($this->message)
            ->line("Level: {$this->level}")
            ->line('Please check the system logs for more details.');
    }
}