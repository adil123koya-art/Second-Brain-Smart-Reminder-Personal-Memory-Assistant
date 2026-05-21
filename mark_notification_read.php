<?php
session_start();
include('db.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];
    $user_email = $_SESSION['email'];
    
    // Mark notification as read
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE notification_id = ? AND user_email = ?");
    $stmt->bind_param("is", $notification_id, $user_email);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?> 