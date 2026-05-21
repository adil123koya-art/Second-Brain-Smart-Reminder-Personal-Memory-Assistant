<?php
session_start();
include('db.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND is_admin=1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row["password"])) {
            $_SESSION["email"] = $email;
            $_SESSION["name"] = $row["name"];
            $_SESSION["is_admin"] = true;

            header("Location: admin_dashboard.php");
            exit;
        } else {
            $message = "<div class='message error'>❌ Invalid password.</div>";
        }
    } else {
        $message = "<div class='message error'>❌ Admin access denied. Only administrators can access this area.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="icon" type="image/png" href="logo.png">
  <title>Admin Login | Second Brain | Your Professional Reminder Solution</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #181F2A 0%, #232B3E 100%);
      color: white;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      background-image: url('logo.png');
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover;
    }

    .container {
      background: rgba(35, 43, 62, 0.75);
      padding: 56px 36px 44px 36px;
      border-radius: 24px;
      box-shadow: 0 8px 32px rgba(31,116,231,0.12), 0 2px 8px rgba(32,201,151,0.10);
      text-align: center;
      max-width: 480px;
      width: 100%;
      position: relative;
      z-index: 1;
      backdrop-filter: blur(2.5px);
      border: 1.5px solid rgba(31,116,231,0.10);
    }

    .admin-badge {
      background: linear-gradient(135deg, #e74545 0%, #b83232 100%);
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 20px;
      letter-spacing: 1px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #1f74e7;
    }

    input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      background: #111;
      border: 1px solid #444;
      color: white;
      border-radius: 6px;
    }

    .btn {
      width: 100%;
      background: linear-gradient(135deg, #e74545 0%, #b83232 100%);
      color: white;
      border: none;
      padding: 10px;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      font-weight: 600;
    }

    .btn:hover {
      background: linear-gradient(135deg, #b83232 0%, #8a2525 100%);
    }

    .message {
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 6px;
      text-align: center;
      font-size: 14px;
    }

    .message.error {
      background-color: rgba(231, 69, 69, 0.2);
      color: #ff6b6b;
      border: 1px solid rgba(231, 69, 69, 0.3);
    }

    p {
      text-align: center;
      margin-top: 10px;
      font-size: 14px;
    }

    p a {
      color: #1f74e7;
      text-decoration: none;
    }

    p a:hover {
      text-decoration: underline;
    }

    .back-link {
      position: absolute;
      top: 20px;
      left: 20px;
      color: #AAB4C3;
      text-decoration: none;
      font-size: 14px;
      transition: color 0.3s;
    }

    .back-link:hover {
      color: #1f74e7;
    }
  </style>
</head>
<body>

<a href="index.html" class="back-link">← Back to Home</a>

<div class="container">
  <div class="admin-badge">ADMIN ACCESS</div>
  <h2>Admin Login</h2>

  <?php if (!empty($message)) echo $message; ?>

  <form method="POST">
    <input type="email" name="email" placeholder="Admin Email" required>
    <input type="password" name="password" placeholder="Admin Password" required>
    <button type="submit" class="btn">Login as Admin</button>
  </form>

  <p>Regular user? <a href="login.php">Login here</a></p>
</div>

</body>
</html> 