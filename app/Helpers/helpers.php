<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
# use PHPMailer\PHPMailer\Exception;


/* -------- SEND EMAIL ----------*/
if ( !function_exists('sendEmail') ){

    /**
     * @throws Exception
     */
    function sendEmail(array $emailConfig ): bool
    {
        /*require 'vendor/PHPMailer/PHPMailer/src/Exception.php';

        require 'vendor/PHPMailer/PHPMailer/src/PHPMailer.php';
        require 'vendor/PHPMailer/PHPMailer/src/SMTP.php';
        $phpmailer = new PHPMailer();
$phpmailer->isSMTP();
$phpmailer->Host = 'sandbox.smtp.mailtrap.io';
$phpmailer->SMTPAuth = true;
$phpmailer->Port = 2525;
$phpmailer->Username = '845af91ff93a71';
$phpmailer->Password = 'ffccc6fcb01863';
        */

        $email = new PHPMailer( TRUE );
        $email->SMTPDebug = 0;
        $email->isSMTP();
        $email->Host = env('EMAIL_HOST');
        $email->SMTPAuth = TRUE;
        $email->Username = env('EMAIL_USERNAME');
        $email->Password = env('EMAIL_PASSWORD');
        $email->SMTPSecure = env('EMAIL_ENCRYPTION');
        $email->Port = env('EMAIL_PORT');
        $email->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
        $email->addAddress($emailConfig['recipient_email'], $emailConfig['recipient_name']);
        $email->isHTML( TRUE );
        $email->Subject = $emailConfig['subject'];
        $email->Body = $emailConfig['body'];

        return $email->send();
    }
}
