<?php
include('db.php');
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<h2>Test Real Reminder with Voice Attachment</h2>";

// Get a reminder with voice file
$stmt = $conn->prepare("SELECT * FROM reminders WHERE voice_file IS NOT NULL AND voice_file != '' ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ No reminders with voice files found<br>";
    exit;
}

$row = $result->fetch_assoc();
$email = $row['user_email'];
$title = $row['title'];
$desc = $row['description'];
$voice = $row['voice_file'];

echo "<h3>Testing with reminder:</h3>";
echo "Email: $email<br>";
echo "Title: $title<br>";
echo "Voice file: $voice<br>";

// Test if voice file exists
if (file_exists($voice)) {
    echo "✅ Voice file exists<br>";
    echo "File size: " . filesize($voice) . " bytes<br>";
} else {
    echo "❌ Voice file does not exist: $voice<br>";
    exit;
}

// Test email sending with detailed logging
$mail = new PHPMailer(true);
try {
    echo "<h3>Setting up email...</h3>";
    
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'secondsbrain@gmail.com';
    $mail->Password   = 'xeplxspxrpwwduef';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('secondsbrain@gmail.com', 'Second Brain');
    $mail->addAddress($email);
    $mail->addReplyTo('noreply@secondbrain.com', 'Do Not Reply');

    $mail->Subject = "⏰ Reminder: $title";
    
    // Create HTML body (same as in send_due_reminders.php)
    $htmlBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;'>
        <div style='background-color: #1f74e7; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='margin: 0; font-size: 24px;'>⏰ SecondBrain Reminder</h1>
        </div>
        <div style='background-color: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <h2 style='color: #1f74e7; margin-top: 0;'>$title</h2>
            <p style='color: #666; font-size: 16px; line-height: 1.6;'>$desc</p>
            <div style='background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <p style='margin: 0; color: #666;'><strong>📅 Date:</strong> " . date('F d, Y', strtotime($row['reminder_date'])) . "</p>
                <p style='margin: 5px 0 0 0; color: #666;'><strong>🕐 Time:</strong> " . date('h:i A', strtotime($row['reminder_time'])) . "</p>
            </div>";
    
    if (!empty($voice)) {
        $htmlBody .= "
            <div style='background-color: #e8f4fd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #1f74e7;'>
                <p style='margin: 0; color: #1f74e7;'><strong>🎤 Voice Note:</strong> A voice note has been attached to this reminder.</p>
            </div>";
    }
    
    $htmlBody .= "
            <div style='text-align: center; margin-top: 30px;'>
                <p style='color: #999; font-size: 14px;'>This reminder was sent from your SecondBrain account.</p>
            </div>
        </div>
    </div>";
    
    $mail->isHTML(true);
    $mail->Body = $htmlBody;
    
    // Plain text version
    $plainTextBody = "⏰ SecondBrain Reminder\n\n";
    $plainTextBody .= "Title: $title\n";
    $plainTextBody .= "Description: $desc\n";
    $plainTextBody .= "Date: " . date('F d, Y', strtotime($row['reminder_date'])) . "\n";
    $plainTextBody .= "Time: " . date('h:i A', strtotime($row['reminder_time'])) . "\n";
    if (!empty($voice)) {
        $plainTextBody .= "Voice Note: A voice note has been attached to this reminder.\n";
    }
    $plainTextBody .= "\nThis reminder was sent from your SecondBrain account.";
    
    $mail->AltBody = $plainTextBody;
    
    echo "✅ Email setup complete<br>";
    
    // Add voice attachment with detailed logging
    echo "<h3>Adding voice attachment...</h3>";
    $attachmentPath = $voice;
    $attachmentName = basename($voice);
    
    echo "Attachment path: $attachmentPath<br>";
    echo "Attachment name: $attachmentName<br>";
    echo "File size: " . filesize($attachmentPath) . " bytes<br>";
    
    if (file_exists($attachmentPath)) {
        $attachmentResult = $mail->addAttachment($attachmentPath, $attachmentName);
        if ($attachmentResult) {
            echo "✅ Voice attachment added successfully<br>";
        } else {
            echo "❌ Failed to add voice attachment<br>";
        }
        
        // Check if attachment was actually added
        $attachments = $mail->getAttachments();
        echo "Total attachments in email: " . count($attachments) . "<br>";
        
        if (count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                echo "Attachment: " . $attachment[0] . " (Name: " . $attachment[2] . ")<br>";
            }
        }
    } else {
        echo "❌ Voice attachment file not found<br>";
    }
    
    echo "<h3>Sending email...</h3>";
    
    if ($mail->send()) {
        echo "✅ Email sent successfully to $email<br>";
        echo "✅ Voice attachment included: $attachmentName<br>";
    } else {
        echo "❌ Email sending failed: " . $mail->ErrorInfo . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "<br>";
    echo "Mailer Error: " . $mail->ErrorInfo . "<br>";
}

echo "<h3>Test completed!</h3>";
echo "<p>Check your email to see if the voice attachment was received.</p>";
echo "<p>If you still don't see the attachment, it might be blocked by Gmail.</p>";
?> 