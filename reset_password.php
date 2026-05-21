<?php
include('db.php');
$message = '';
$show_form = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT user_id, reset_expires FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['reset_expires']) > time()) {
            $show_form = true;
            $user_id = $row['user_id'];
        } else {
            $message = "<div class='message error'>Reset link expired. Please request a new one.</div>";
        }
    } else {
        $message = "<div class='message error'>Invalid reset link.</div>";
    }
} else {
    $message = "<div class='message error'>No reset token provided.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    if (empty($password) || strlen($password) < 6) {
        $message = "<div class='message error'>Password must be at least 6 characters.</div>";
        $show_form = true;
    } elseif ($password !== $confirm) {
        $message = "<div class='message error'>Passwords do not match.</div>";
        $show_form = true;
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE user_id=?");
        $update->bind_param("si", $hashed, $user_id);
        if ($update->execute()) {
            $message = "<div class='message success'>Password reset successful! <a href='login.php'>Login now</a>.</div>";
            $show_form = false;
        } else {
            $message = "<div class='message error'>Error updating password.</div>";
            $show_form = true;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Reset Password | Second Brain</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .container { max-width: 400px; margin: 60px auto; background: #232B3E; border-radius: 12px; box-shadow: 0 4px 16px rgba(24,31,42,0.12); padding: 30px 25px; }
    h2 { color: #1f74e7; margin-bottom: 18px; }
    label { display: block; margin-bottom: 6px; color: #AAB4C3; }
    input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; background: #181F2A; border: 1px solid #283046; border-radius: 6px; color: #F5F7FA; }
    .btn { width: 100%; padding: 10px; background: #1f74e7; color: #fff; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
    .btn:hover { background: #1455b8; }
    .message.success { background: #20C997; color: #fff; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
    .message.error { background: #e74545; color: #fff; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
  </style>
</head>
<body>
<div class="container">
    <h2>Reset Password</h2>
    <?php if (!empty($message)) echo $message; ?>
    <?php if ($show_form): ?>
    <form method="POST">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
        <label>New Password</label>
        <input type="password" name="password" required>
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>
        <button type="submit" class="btn">Reset Password</button>
    </form>
    <?php endif; ?>
    <p><a href="login.php" class="btn" style="margin-top:12px; background:#20C997;">Back to Login</a></p>
</div>
</body>
</html> 