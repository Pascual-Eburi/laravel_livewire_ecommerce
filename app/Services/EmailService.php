<?php

// EmailService.php
namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use ConstDefaults;

class EmailService {
    /**
     * @throws Exception
     */
    public function sendPasswordResetEmail( $user, $reset_link): bool
    {
        $data = [
            'reset_link' => $reset_link,
            'user' => $user,
            'tokenExpiredMinutes' => ConstDefaults::tokenExpiredMinutes
        ];
        $email_body = view('email-templates.forgot_password', $data)
            ->render();

        $emailConfig = [
            'from_email' => env('EMAIL_FROM_ADDRESS'),
            'from_name' => env('EMAIL_FROM_NAME'),
            'recipient_email' => $user->email,
            'recipient_name' => $user->first_name . ' '. $user->last_name,
            'subject' => 'Reset Password',
            'body' => $email_body
        ];

        return sendEmail($emailConfig);
    }

    /**
     * @throws Exception
     */
    public function sendPasswordChangedEmail($user): bool
    {
        // Send email to notify user
        $data = [
            'user' => $user,
            'login_link' => route('auth.login')
        ];

        $email_body = view('email-templates.reset_password_notification', $data)->render();

        $email_config = [
            'from_email' => env('EMAIL_FROM_ADDRESS'),
            'from_name' => env('EMAIL_FROM_NAME'),
            'recipient_email' => $user->email,
            'recipient_name' => $user->first_name . ' '. $user->last_name,
            'subject' => 'Password Changed',
            'body' => $email_body
        ];

        return sendEmail($email_config);
    }
}






