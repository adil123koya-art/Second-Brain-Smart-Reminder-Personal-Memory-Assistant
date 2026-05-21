<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit;
}

// Check if current user is admin
$email = $_SESSION["email"];
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user || $user['is_admin'] != 1) {
    header("Location: dashboard.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    // Delete all reminders for this user
    $reminder_del = $conn->prepare("DELETE FROM reminders WHERE user_email = (SELECT email FROM users WHERE user_id = ?)");
    $reminder_del->bind_param("i", $id);
    $reminder_del->execute();

    // Now delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // success
    } else {
        echo "Error deleting user: " . $stmt->error;
        exit;
    }
}
header("Location: admin_dashboard.php");
exit(); 