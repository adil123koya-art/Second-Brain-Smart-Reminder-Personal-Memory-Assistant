<?php
include('db.php');
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<h2>Debug Voice File Attachment</h2>";

// Get the specific reminder that was sent
$voiceFile = 'uploads/voice_1754460990.webm';
$email = 'adil123koya@gmail.com';

echo "<h3>Testing voice file: $voiceFile</h3>";

// Check if file exists
if (file_exists($voiceFile)) {
    echo "✅ File exists<br>";
    echo "File size: " . filesize($voiceFile) . " bytes<br>";
    echo "File permissions: " . substr(sprintf('%o', fileperms($voiceFile)), -4) . "<br>";
    
    if (is_readable($voiceFile)) {
        echo "✅ File is readable<br>";
    } else {
        echo "❌ File is NOT readable<br>";
    }
} else {
    echo "❌ File does not exist<br>";
    exit;
}

// Test PHPMailer attachment
$mail = new PHPMailer(true);
try {
    echo "<h3>Testing PHPMailer attachment...</h3>";
    
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'secondsbrain@gmail.com';
    $mail->Password   = 'xeplxspxrpwwduef';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('secondsbrain@gmail.com', 'Second Brain');
    $mail->addAddress($email);
    
    $mail->Subject = "🔧 DEBUG: Voice Attachment Test";
    $mail->Body = "This is a debug test to check voice file attachment.";
    
    echo "✅ Email setup complete<br>";
    
    // Try to add attachment
    echo "Adding attachment: $voiceFile<br>";
    $attachmentName = basename($voiceFile);
    echo "Attachment name: $attachmentName<br>";
    
    $result = $mail->addAttachment($voiceFile, $attachmentName);
    if ($result) {
        echo "✅ Attachment added successfully<br>";
    } else {
        echo "❌ Failed to add attachment<br>";
    }
    
    // Check if attachment was actually added
    $attachments = $mail->getAttachments();
    echo "Number of attachments: " . count($attachments) . "<br>";
    
    if (count($attachments) > 0) {
        foreach ($attachments as $attachment) {
            echo "Attachment: " . $attachment[0] . " (Name: " . $attachment[2] . ")<br>";
        }
    }
    
    echo "<h3>Sending test email...</h3>";
    
    if ($mail->send()) {
        echo "✅ Email sent successfully with attachment<br>";
    } else {
        echo "❌ Email sending failed: " . $mail->ErrorInfo . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
}

echo "<h3>Debug completed!</h3>";
?> 