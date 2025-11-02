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


// Send Research Submission Notification
 if (!function_exists('sendSubmissionNotification')) {
    function sendSubmissionNotification($conn, $research_email) {
        
        $stmt = $conn->prepare("
            SELECT research_email FROM research_tbl WHERE research_email= ?
        ");
        $stmt->bind_param("s", $research_email);
        $stmt->execute();
        $result = $stmt->get_result();


        $row = $result->fetch_assoc();
        $recipient = $row['research_email'];
        $stmt->close();

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'genderanddevelopment.neust@gmail.com';
            $mail->Password = 'fxso coyx cjiz cpzz'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

  
            $mail->setFrom('genderanddevelopment.neust@gmail.com', 'NEUST GAD Portal');
            $mail->addAddress($recipient);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Research Submission';
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

if (!function_exists('sendResearchApprovalEmail')) {
    function sendResearchApprovalEmail($conn, $researchId) {
        $mail = new PHPMailer(true);

        // Fetch research info (email + title)
        $stmt = $conn->prepare("
            SELECT research_email, research_title
            FROM research_tbl
            WHERE id = ?
        ");
        $stmt->bind_param("i", $researchId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            error_log("❌ No research found for ID: $researchId");
            return false;
        }

        $row = $result->fetch_assoc();
        $recipient = $row['research_email'];
        $researchTitle = $row['research_title'];

        try {
            // SMTP setup
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'genderanddevelopment.neust@gmail.com';
            $mail->Password   = 'fxso coyx cjiz cpzz'; // App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Email content
            $mail->setFrom('genderanddevelopment.neust@gmail.com', 'NEUST GAD Portal');
            $mail->addAddress($recipient);
            $mail->isHTML(true);
            $mail->Subject = 'Research Approval Notification';
            $mail->Body    = "
                <p>Dear Researcher,</p>
                <p>We are pleased to inform you that your research titled 
                <b>\"{$researchTitle}\"</b> ahs been approved by the panel.</p> <br>
                <p>You may now proceed to the next phase as instructed by the GAD Office.</p>
                <br>
                <p>Congratulations!</p>
                <p>Regards,<br><b>NEUST GAD Office</b></p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('Mailer Error (Approval): ' . $mail->ErrorInfo);
            return false;
        }
    }
}


// Send email when research is rejected
if (!function_exists('sendResearchRejectionEmail')) {
    function sendResearchRejectionEmail($conn, $researchId) {
        $mail = new PHPMailer(true);

        $stmt = $conn->prepare("
            SELECT research_email, research_title
            FROM research_tbl
            WHERE id = ?
        ");
        $stmt->bind_param("i", $researchId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            error_log("❌ No research found for ID: $researchId");
            return false;
        }

        $row = $result->fetch_assoc();
        $recipient = $row['research_email'];
        $researchTitle = $row['research_title'];

        try {
            // SMTP setup
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'genderanddevelopment.neust@gmail.com';
            $mail->Password   = 'fxso coyx cjiz cpzz'; // App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Email content
            $mail->setFrom('genderanddevelopment.neust@gmail.com', 'NEUST GAD Portal');
            $mail->addAddress($recipient);
            $mail->isHTML(true);
            $mail->Subject = 'Research Rejection Notification';
            $mail->Body    = "
                <p>Dear Researcher,</p>
                <p>We regret to inform you that your research titled 
                <b>\"{$researchTitle}\"</b> has been rejected by the panel.</p>
                <br>
                <p>Please review the comments and make the necessary revisions before resubmission.</p>
                <p>Regards,<br><b>NEUST GAD Office</b></p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('Mailer Error (Rejection): ' . $mail->ErrorInfo);
            return false;
        }
    }
}