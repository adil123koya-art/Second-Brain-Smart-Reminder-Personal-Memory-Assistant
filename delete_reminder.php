<?php
session_start();
include('db.php');

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Only delete if the reminder belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM reminders WHERE reminder_id = ? AND user_email = ?");
    $stmt->bind_param("is", $id, $email);
    $stmt->execute();
}

header('Location: view_reminder.php');
exit();
?> 