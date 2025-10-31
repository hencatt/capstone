<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once "gad_portal.php";
require_once '../Phpmailer/src/PHPMailer.php';
require_once '../Phpmailer/src/Exception.php';
require_once '../Phpmailer/src/SMTP.php';


// Send email for account creation
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

            $fullName = trim($fname . " " . $lname);

            $mail->Body = "
                Hello, <b>{$fullName}</b>! <br><br>
                Your account has been created.<br><br>
                <b>Username:</b> {$username}<br>
                <b>Password:</b> {$pass}<br><br>
                Please login with this link:<br>
                <a href='http://localhost/capstone'>NEUST GAD Portal</a>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}


 if (!function_exists('sendSubmissionNotification')) {
    function sendSubmissionNotification($conn, $email) {
        
        $stmt = $conn->prepare("
            SELECT email FROM accounts_tbl WHERE email = ?
            UNION
            SELECT email FROM employee_tbl WHERE email = ?
        ");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "<script>alert('⚠️ No email address found in the database. Please use a registered email.');</script>";
            error_log("No email found for: $email");
            return false;
        }


        $row = $result->fetch_assoc();
        $recipient = $row['email'];
        $stmt->close();

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'genderanddevelopment.neust@gmail.com';
            $mail->Password = 'fxso coyx cjiz cpzz'; // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('genderanddevelopment.neust@gmail.com', 'NEUST GAD Portal');
            $mail->addAddress($recipient);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Research Submission Notification';
            $mail->Body    = '
                <p>Dear Researcher,</p>
                <p>Your research has been successfully submitted. Please wait for evaluation.</p>
                <br>
                <p>Regards,<br><b>NEUST GAD Office</b></p>
            ';

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            echo "<script>alert('❌ Failed to send email notification.');</script>";
            return false;
        }
    }
}