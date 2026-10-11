<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Branded password-reset email for InvoiceFlow. Extends the framework's
 * notification so the token / URL logic stays the same, and only the
 * wording and CTA button are customised.
 */
class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expire = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Reset your InvoiceFlow password')
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a password reset request for your InvoiceFlow account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in ' . $expire . ' minutes.')
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Thanks, the InvoiceFlow team');
    }
}
