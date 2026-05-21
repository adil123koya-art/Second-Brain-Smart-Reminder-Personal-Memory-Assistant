<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST["name"];
    $email    = $_POST["email"];
    $phone    = $_POST["phone"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    if (!$check) {
        $message = "<div class='message error'>Prepare failed: " . $conn->error . "</div>";
    } else {
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "<div class='message error'>❌ Email already registered. <a href='login.php'>Login here</a></div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                $message = "<div class='message error'>Prepare failed: " . $conn->error . "</div>";
            } else {
                $stmt->bind_param("ssss", $name, $email, $phone, $password);

                if ($stmt->execute()) {
                    $message = "<div class='message success'>✅ Registered successfully. <a href='login.php'>Login Now</a></div>";
                } else {
                    $message = "<div class='message error'>❌ Error: " . $stmt->error . "</div>";
                }
            }
        }
        $check->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Register | SecondBrain</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: radial-gradient(circle at 30% 20%, rgba(31, 116, 231, 0.05) 0%, transparent 40%),
                        radial-gradient(circle at 70% 80%, rgba(32, 201, 151, 0.05) 0%, transparent 40%);
        }

        .auth-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            box-shadow: 0 8px 32px rgba(31,116,231,0.12);
            width: 100%;
            max-width: 480px;
            padding: 2.5rem;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .welcome-icon {
            font-size: 3rem;
            color: white;
            margin-bottom: 1rem;
        }

        .auth-title {
            font-size: 1.8rem;
            color: white;
            margin: 1rem 0;
            font-weight: 600;
        }

        .auth-subtitle {
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .register-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            margin: 0 auto;
            max-width: 380px;
        }

        .form-group {
            margin-bottom: 0;
            position: relative;
            width: 100%;
        }

        .form-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .form-control {
            padding-left: 3rem;
            background: rgba(15, 20, 25, 0.6);
            border: 1px solid rgba(31, 116, 231, 0.2);
            height: 3.2rem;
            width: 100%;
            font-size: 1rem;
            color: white;
            border-radius: 8px;
        }

        .form-control:focus {
            outline: none;
            background: rgba(15, 20, 25, 0.8);
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(31, 116, 231, 0.2);
        }

        .form-control::placeholder {
            color: var(--text-light);
        }

        .btn-register {
            width: 100%;
            height: 3.2rem;
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(31, 116, 231, 0.4);
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
        }

        .auth-footer a {
            color: #1f74e7;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .auth-footer a:hover {
            color: #1455b8;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 2rem 0;
            color: var(--text-light);
            width: 100%;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(31, 116, 231, 0.1);
        }

        .divider span {
            padding: 0 1rem;
            font-size: 0.9rem;
        }

        .social-login {
            display: flex;
            gap: 1rem;
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
        }

        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border: 1px solid rgba(31, 116, 231, 0.2);
            border-radius: 12px;
            background: transparent;
            color: var(--text-light);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            background: rgba(31, 116, 231, 0.1);
            transform: translateY(-2px);
        }

        .message {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            max-width: 380px;
            margin-left: auto;
            margin-right: auto;
        }

        .message.error {
            background: rgba(231, 69, 69, 0.1);
            border: 1px solid rgba(231, 69, 69, 0.2);
            color: #ff6b6b;
        }

        .message.success {
            background: rgba(32, 201, 151, 0.1);
            border: 1px solid rgba(32, 201, 151, 0.2);
            color: #20c997;
        }

        .message a {
            color: inherit;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="welcome-icon">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join SecondBrain and start organizing your life</p>
            </div>

            <?php if (isset($message)) {
                $icon = strpos($message, "error") !== false ? "fa-circle-exclamation" : "fa-circle-check";
                echo str_replace('class="message', 'class="message"><i class="fas ' . $icon . '"></i>', $message);
            } ?>

            <form method="POST" class="register-form">
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="name" class="form-control" required placeholder="Full Name">
                </div>

                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" required placeholder="Email Address">
                </div>

                <div class="form-group">
                    <i class="fas fa-phone"></i>
                    <input type="text" name="phone" class="form-control" required placeholder="Phone Number">
                </div>

                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" required placeholder="Password">
                </div>

                <button type="submit" class="btn-register">
                    Create Account
                </button>
            </form>

            <div class="divider">
                <span>or sign up with</span>
            </div>

            <div class="social-login">
                <button class="social-btn">
                    <i class="fab fa-google"></i>
                    Google
                </button>
                <button class="social-btn">
                    <i class="fab fa-facebook"></i>
                    Facebook
                </button>
            </div>

            <div class="auth-footer">
                <p style="color: var(--text-light);">
                    Already have an account? 
                    <a href="login.php">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
