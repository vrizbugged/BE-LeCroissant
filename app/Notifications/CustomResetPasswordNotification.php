<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');
        $email = urlencode($notifiable->getEmailForPasswordReset());
        $resetUrl = "{$frontendUrl}/reset-password?token={$this->token}&email={$email}";
        $appUrl = rtrim((string) config('app.url', 'http://localhost:8000'), '/');
        $logoUrl = (string) env('RESET_PASSWORD_LOGO_URL', "{$appUrl}/apple-touch-icon.png");
        $logoDataUri = null;
        $logoPath = public_path('apple-touch-icon.png');
        if (is_file($logoPath)) {
            $mimeType = mime_content_type($logoPath) ?: 'image/png';
            $logoDataUri = 'data:'.$mimeType.';base64,'.base64_encode((string) file_get_contents($logoPath));
        }
        $expireMinutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Reset Password - Le Croissant')
            ->view('emails.reset-password', [
                'name' => $notifiable->name ?? 'Customer',
                'resetUrl' => $resetUrl,
                'logoUrl' => $logoUrl,
                'logoDataUri' => $logoDataUri,
                'expireMinutes' => $expireMinutes,
                'appName' => config('app.name', 'Le Croissant'),
            ]);
    }
}
