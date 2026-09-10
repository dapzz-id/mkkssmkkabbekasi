<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ResetPasswordNotification
 *
 * Sends a professional, branded reset-password email.
 * Implements ShouldQueue so Laravel dispatches it to the
 * database queue — the HTTP request returns immediately
 * while the background worker handles SMTP delivery.
 */
class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param string $token  The secure, hashed password-reset token
     */
    public function __construct(
        protected string $token
    ) {}

    /**
     * The notification should be queued (background delivery).
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the reset-password email.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi — MKKS SMK Kabupaten Bekasi')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun MKKS SMK Kabupaten Bekasi yang terdaftar dengan alamat email ini.')
            ->action('Atur Ulang Kata Sandi', $resetUrl)
            ->line('Tautan ini akan kedaluwarsa dalam **' . $expireMinutes . ' menit**.')
            ->line('Jika Anda tidak meminta pengaturan ulang kata sandi, tidak ada tindakan yang perlu dilakukan dan akun Anda tetap aman.')
            ->salutation('— Tim MKKS SMK Kabupaten Bekasi');
    }
}
