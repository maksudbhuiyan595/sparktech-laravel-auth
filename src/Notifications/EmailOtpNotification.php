<?php

declare(strict_types=1);

namespace Sparktech\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class EmailOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $otp,
        private readonly int $expiresInMinutes = 5,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your verification code')
            ->greeting('Hello!')
            ->line('Your email verification OTP is:')
            ->line('**' . $this->otp . '**')
            ->line("This OTP will expire in {$this->expiresInMinutes} minutes.")
            ->line('If you did not create this account, you can safely ignore this email.');
    }
}