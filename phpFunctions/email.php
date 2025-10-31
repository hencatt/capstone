<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once "gad_portal.php";
require_once '../Phpmailer/src/PHPMailer.php';
require_once '../Phpmailer/src/Exception.php';
require_once '../Phpmailer/src/SMTP.php';

if (!function_exists('sendUserCredentials')) {
    function sendUserCredentials($email, $username, $pass, $fname, $lname) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'genderanddevelopment.neust@gmail.com';
            $mail->Password   = 'fxso coyx cjiz cpzz'; // 16-character App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('genderanddevelopment.neust@gmail.com', 'NEUST GAD Portal');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your Account Credentials';

            // Combine name properly
            $fullName = trim($fname ." ". $lname);

            $mail->Body = "
                Hello, <b>{$fullName}</b>! <br><br>
                Your account has been created.<br><br>
                <b>Username:</b> {$username}<br>
                <b>Password:</b> {$pass}<br><br>
                Please login with this link:<br>
                <a href='http://localhost/capstone'>NEUST GAD Portal</a> <!-- Update this link after deployment -->
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}