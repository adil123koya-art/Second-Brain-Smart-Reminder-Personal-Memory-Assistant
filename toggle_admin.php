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
$admin = isset($_GET['admin']) ? intval($_GET['admin']) : 0;
if ($id > 0) {
    $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE user_id = ?");
    $stmt->bind_param("ii", $admin, $id);
    $stmt->execute();
}
header("Location: admin_dashboard.php");
exit(); 