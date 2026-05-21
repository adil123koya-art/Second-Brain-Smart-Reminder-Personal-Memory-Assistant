<?php
include('db.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id'])) {
    http_response_code(400);
    error_log("mark_reminder_seen.php: Missing reminder ID");
    exit('Missing reminder ID');
}

$reminder_id = intval($_GET['id']);

// Update is_seen flag for the reminder
$stmt = $conn->prepare("UPDATE reminders SET is_seen = 1 WHERE reminder_id = ?");
if (!$stmt) {
    http_response_code(500);
    error_log("mark_reminder_seen.php: Database prepare error - " . $conn->error);
    exit('Database error');
}
$stmt->bind_param("i", $reminder_id);
if (!$stmt->execute()) {
    error_log("mark_reminder_seen.php: Database execute error - " . $stmt->error);
} else {
    error_log("mark_reminder_seen.php: Successfully updated is_seen for reminder_id $reminder_id");
}

// Log successful update
error_log("mark_reminder_seen.php: Updated is_seen for reminder_id $reminder_id");

// Return a 1x1 transparent GIF as tracking pixel
header('Content-Type: image/gif');
echo base64_decode(
    'R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='
);
exit;
?>
