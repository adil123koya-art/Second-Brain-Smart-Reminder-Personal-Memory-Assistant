<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendReminderEmail($to, $subject, $body, $voicePath = null) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'secondsbrain@gmail.com';       // 🔁 Your Gmail
        $mail->Password   = 'skfyoaymejehkbxk';          // 🔁 Your Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender & recipient
        $mail->setFrom('secondsbrain@gmail.com', 'Second Brain Reminder');
        $mail->addAddress($to);

        // Attach voice note if available
        if ($voicePath) {
            $mail->addAttachment($voicePath);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
