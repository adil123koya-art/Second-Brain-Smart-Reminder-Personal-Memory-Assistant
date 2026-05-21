<?php
// contact.php: Handles contact form submissions from index.html

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'db.php';

// Set your admin email here
$admin_email = 'secondsbrain@gmail.com'; // Updated to your real email

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $error = '';
    if (!$name || !$email || !$message) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    }

    if ($error) {
        show_response(false, $error);
        exit;
    }

    // Store complaint in database
    try {
        $stmt = $conn->prepare("INSERT INTO complaints (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        $stmt->execute();
    } catch (Exception $e) {
        // Continue with email even if database insert fails
        error_log("Failed to store complaint in database: " . $e->getMessage());
    }

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change if not using Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'secondsbrain@gmail.com'; // TODO: Change to your email
        $mail->Password = 'xeplxspxrpwwduef'; // TODO: Use app password or real password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress($admin_email, 'Admin');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Contact Form Submission from Second Brain';
        $mail->Body    = '<b>Name:</b> ' . htmlspecialchars($name) . '<br><b>Email:</b> ' . htmlspecialchars($email) . '<br><b>Message:</b><br>' . nl2br(htmlspecialchars($message));
        $mail->AltBody = "Name: $name\nEmail: $email\nMessage:\n$message";

        $mail->send();
        show_response(true, 'Thank you! Your message has been sent.');
    } catch (Exception $e) {
        show_response(false, 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
    }
} else {
    header('Location: index.html');
    exit;
}

function show_response($success, $msg) {
    // Check if user is logged in
    session_start();
    $redirect_url = isset($_SESSION['email']) ? 'dashboard.php' : 'index.html';
    $button_text = isset($_SESSION['email']) ? 'Back to Dashboard' : 'Back to Home';
    
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Contact Us - Second Brain</title><link rel="stylesheet" href="style.css"></head><body style="background:#1a2235;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;">';
    echo '<div style="background:rgba(35,43,62,0.97);padding:36px 28px;border-radius:18px;box-shadow:0 8px 32px rgba(31,116,231,0.12);max-width:400px;width:90vw;text-align:center;">';
    echo $success ? '<h2 style="color:#20C997;">Success</h2>' : '<h2 style="color:#e74c3c;">Error</h2>';
    echo '<p style="margin:18px 0 0 0;font-size:1.1rem;">' . htmlspecialchars($msg) . '</p>';
    echo '<a href="' . $redirect_url . '" style="display:inline-block;margin-top:24px;padding:10px 24px;background:#1f74e7;color:#fff;font-weight:700;border:none;border-radius:8px;text-decoration:none;font-size:1.08rem;">' . $button_text . '</a>';
    echo '</div></body></html>';
} 