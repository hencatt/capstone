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

function sendUserCredentials($email, $username, $password) {
    $con = con(); // Get DB connection

    // Fetch SMTP settings from accounts_tbl (adjust query as needed)
    $stmt = $con->prepare("SELECT email, pass FROM accounts_tbl WHERE position = ? LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($smtpUsername, $smtpPassword);
    $stmt->fetch();
    $stmt->close();

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUsername; 
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom($smtpUsername, 'Your App Name');
        $mail->addAddress($email);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Account Credentials';
        $mail->Body    = "Hello,<br>Your username: <b>$username</b><br>Your password: <b>$password</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}