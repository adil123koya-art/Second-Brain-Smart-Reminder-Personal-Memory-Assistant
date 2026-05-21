<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer from local source
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Database connection
$conn = new mysqli("localhost", "root", "", "second_brain_v2");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Kolkata');

// File paths for logs
$logFile = __DIR__ . DIRECTORY_SEPARATOR . 'cron_run.log';

// Create log file if missing
if (!file_exists($logFile)) file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Cron log created\n");

$now = date('Y-m-d H:i:s');
$logMessage = "[" . $now . "] Cron started\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

// Fetch due reminders (precise check for date and time)
$sql = "SELECT * FROM reminders 
        WHERE (reminder_date < CURDATE() OR (reminder_date = CURDATE() AND reminder_time <= CURTIME())) 
        AND is_sent = 0";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $toEmail = $row['user_email'];
        $subject = "Reminder: " . $row['title'];
        $message = $row['description'];
        $voiceFile = $row['voice_file'];
        $reminderId = $row['reminder_id'];

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'secondsbrain@gmail.com'; 
            $mail->Password   = 'skfyoaymejehkbxk'; // App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('secondsbrain@gmail.com', 'Second Brain');
            $mail->addAddress($toEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = "<h3>Reminder: " . htmlspecialchars($row['title']) . "</h3><p>" . nl2br(htmlspecialchars($message)) . "</p>";

            // Attach voice file if exists
            if (!empty($voiceFile) && file_exists(__DIR__ . DIRECTORY_SEPARATOR . $voiceFile)) {
                $mail->addAttachment(__DIR__ . DIRECTORY_SEPARATOR . $voiceFile);
            }

            // Send the email
            if($mail->send()) {
                // Update DB status
                $update = "UPDATE reminders SET is_sent = 1 WHERE reminder_id = $reminderId";
                $conn->query($update);
                $logMessage = "[" . date('Y-m-d H:i:s') . "] Success: Reminder sent to $toEmail ($row[title])\n";
            }
        } catch (Exception $e) {
            $logMessage = "[" . date('Y-m-d H:i:s') . "] Error sending to $toEmail: {$mail->ErrorInfo}\n";
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
} else {
    $logMessage = "[" . date('Y-m-d H:i:s') . "] No due reminders found.\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$conn->close();
$logMessage = "[" . date('Y-m-d H:i:s') . "] Cron finished\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);
?>
?>
