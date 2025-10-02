<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
include_once 'includes.php';

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

function sendUserCredentials($email, $username, $pass) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'samaniegoivankyle@gmail.com';
        $mail->Password   = 'ruie nobb sqoh fnfa'; // 16-char App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('samaniegoivankyle@gmail.com', 'NEUST GAD Portal');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Account Credentials';
        $mail->Body    = "
            Hello,<br><br>
            Your account has been created.<br><br>
            <b>Username:</b> {$username}<br>
            <b>Password:</b> {$pass}<br><br>
            Please login and change your password immediately.
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}