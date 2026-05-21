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

// Get user to edit
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: admin_dashboard.php");
    exit;
}

$stmt = $conn->prepare("SELECT name, email, phone, is_admin FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$edit_user = $result->fetch_assoc();
if (!$edit_user) {
    header("Location: admin_dashboard.php");
    exit;
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $is_admin = isset($_POST["is_admin"]) ? 1 : 0;
    $new_password = $_POST["password"] ?? '';

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET name=?, phone=?, is_admin=?, password=? WHERE user_id=?");
        $update->bind_param("ssisi", $name, $phone, $is_admin, $hashed_password, $id);
    } else {
        $update = $conn->prepare("UPDATE users SET name=?, phone=?, is_admin=? WHERE user_id=?");
        $update->bind_param("ssii", $name, $phone, $is_admin, $id);
    }
    if ($update->execute()) {
        header("Location: admin_dashboard.php?msg=updated");
        exit;
    } else {
        $message = "<div class='message error'>❌ Error: " . $update->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User | Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container { max-width: 400px; margin: 60px auto; background: #232B3E; border-radius: 12px; box-shadow: 0 4px 16px rgba(24,31,42,0.12); padding: 30px 25px; }
        h2 { color: #1f74e7; margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #AAB4C3; }
        input[type="text"], input[type="email"] { width: 100%; padding: 10px; margin-bottom: 15px; background: #181F2A; border: 1px solid #283046; border-radius: 6px; color: #F5F7FA; }
        .btn { width: 100%; padding: 10px; background: #1f74e7; color: #fff; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #1455b8; }
        .message.error { background: #e74545; color: #fff; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit User</h2>
    <?php if (!empty($message)) echo $message; ?>
    <form method="POST">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($edit_user['name']) ?>" required>
        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($edit_user['email']) ?>" disabled>
        <label>Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($edit_user['phone']) ?>" required>
        <label><input type="checkbox" name="is_admin" <?= $edit_user['is_admin'] ? 'checked' : '' ?>> Admin</label>
        <label>New Password (leave blank to keep current)</label>
        <input type="password" name="password" placeholder="New Password">
        <button type="submit" class="btn">Save Changes</button>
    </form>
    <p><a href="admin_dashboard.php" class="btn" style="margin-top:12px; background:#20C997;">Back</a></p>
</div>
</body>
</html> 