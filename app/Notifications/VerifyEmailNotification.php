<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->name ?? $notifiable->business_name ?? 'there';

        return (new MailMessage)
            ->subject('Email Verification / OTP')
            ->greeting('Email Verification / OTP')
            ->line(new HtmlString('<div style="font-family: \'Helvetica Neue\', Arial, sans-serif; color: #4b5563; font-size: 15px;">'))
            ->line("Hi {$name},")
            ->line('Your Fittingz verification code is:')
            ->line(new HtmlString('<div style="font-size: 26px; font-weight: 800; color: #1f2937; letter-spacing: 3px; margin: 32px 0;">' . $this->code . '</div>'))
            ->line('Enter this code in the app to verify your email address.')
            ->line('This code will expire in 15 minutes.')
            ->line(new HtmlString('<br>'))
            ->line("If you didn't request this, you can safely ignore this email.")
            ->line(new HtmlString('—<br>Fittingz Team'))
            ->line(new HtmlString('</div>'));
    }
}
