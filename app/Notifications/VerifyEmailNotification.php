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
        // Verification emails always send regardless of email_notifications preference
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->name ?? $notifiable->business_name ?? 'there';
        $safeCode = htmlspecialchars($this->code, ENT_QUOTES, 'UTF-8');

        return (new MailMessage)
            ->subject('Email Verification / OTP')
            ->greeting('Email Verification / OTP')
            ->line("Hi {$name},")
            ->line('Your Fittingz verification code is:')
            ->line(new HtmlString('<span style="font-size: 26px; font-weight: 800; color: #1f2937; letter-spacing: 3px;">' . $safeCode . '</span>'))
            ->line('Enter this code in the app to verify your email address.')
            ->line('This code will expire in 15 minutes.')
            ->line("If you didn't request this, you can safely ignore this email.");
    }
}
