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
