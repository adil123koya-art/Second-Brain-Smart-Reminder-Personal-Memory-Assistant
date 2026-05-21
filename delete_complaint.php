<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['complaint_id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$complaint_id = $_GET['complaint_id'];

// Delete the complaint
$stmt = $conn->prepare("DELETE FROM complaints WHERE id = ?");
$stmt->bind_param("i", $complaint_id);

if ($stmt->execute()) {
    header("Location: admin_dashboard.php?msg=complaint_deleted");
} else {
    header("Location: admin_dashboard.php?msg=delete_error");
}
exit;
?> 