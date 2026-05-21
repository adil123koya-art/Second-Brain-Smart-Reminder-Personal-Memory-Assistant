<?php
include('db.php');
$message = '';
$reset_link = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $update = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
        $update->bind_param("sss", $token, $expires, $email);
        $update->execute();
        // Simulate sending email
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset_password.php?token=$token";
        $message = "<div class='message success'>If your email exists, a reset link has been sent.</div>";
    } else {
        $message = "<div class='message error'>If your email exists, a reset link has been sent.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password | Second Brain</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .container { max-width: 400px; margin: 60px auto; background: #232B3E; border-radius: 12px; box-shadow: 0 4px 16px rgba(24,31,42,0.12); padding: 30px 25px; }
    h2 { color: #1f74e7; margin-bottom: 18px; }
    label { display: block; margin-bottom: 6px; color: #AAB4C3; }
    input[type="email"] { width: 100%; padding: 10px; margin-bottom: 15px; background: #181F2A; border: 1px solid #283046; border-radius: 6px; color: #F5F7FA; }
    .btn { width: 100%; padding: 10px; background: #1f74e7; color: #fff; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
    .btn:hover { background: #1455b8; }
    .message.success { background: #20C997; color: #fff; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
    .message.error { background: #e74545; color: #fff; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
  </style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>
    <?php if (!empty($message)) echo $message; ?>
    <form method="POST">
        <?php if (empty($reset_link)): ?>
            <label>Enter your email address</label>
            <input type="email" name="email" required>
        <?php else: ?>
            <div style="margin-bottom:15px; font-size:13px; color:#1f74e7; word-break:break-all;">
                Simulated link: <a href="<?= htmlspecialchars($reset_link) ?>" style="color:#1f74e7; text-decoration:underline;">Reset Password</a>
            </div>
        <?php endif; ?>
        <button type="submit" class="btn">Send Reset Link</button>
    </form>
    <p><a href="login.php" class="btn" style="margin-top:12px; background:#20C997;">Back to Login</a></p>
</div>
</body>
</html> 