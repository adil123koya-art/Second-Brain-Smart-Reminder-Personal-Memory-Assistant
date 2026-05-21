<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Get reminder info before deletion for voice file cleanup
    $stmt = $conn->prepare("SELECT voice_file FROM reminders WHERE reminder_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reminder = $result->fetch_assoc();
    
    // Delete the reminder
    $delete = $conn->prepare("DELETE FROM reminders WHERE reminder_id = ?");
    $delete->bind_param("i", $id);
    
    if ($delete->execute()) {
        // Delete voice file if exists
        if ($reminder && !empty($reminder['voice_file']) && file_exists($reminder['voice_file'])) {
            unlink($reminder['voice_file']);
        }
        header("Location: admin_reminders.php?success=1");
    } else {
        header("Location: admin_reminders.php?error=1");
    }
} else {
    header("Location: admin_reminders.php");
}
exit;
?> 