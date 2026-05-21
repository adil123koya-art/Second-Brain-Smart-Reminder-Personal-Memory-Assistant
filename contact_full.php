<?php
// contact_full.php: Standalone contact/complaint form page
session_start();
include('db.php');

$message = $_SESSION['contact_message'] ?? '';
unset($_SESSION['contact_message']);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['email']);
$user = null;

if ($isLoggedIn) {
    $email = $_SESSION['email'];
    // Fetch user's name and profile picture
    $stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultUser = $stmt->get_result();
    $user = $resultUser->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Contact Us | SecondBrain</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: rgba(35, 43, 62, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
            padding: 0 0.5rem;
        }

        .sidebar-logo i {
            font-size: 1.8rem;
            color: #1f74e7;
        }

        .sidebar-logo span {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f74e7;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(31, 116, 231, 0.1);
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid rgba(31, 116, 231, 0.2);
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            color: var(--text-light);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
        }

        .menu-item.active {
            background: #1f74e7;
            color: white;
        }

        .menu-item i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.8rem;
            color: white;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title i {
            color: #1f74e7;
            font-size: 2rem;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        /* Contact Form Card */
        .contact-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2.5rem;
            max-width: 500px;
            width: 100%;
            position: relative;
            margin: 0 auto;
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
            border-radius: 24px 24px 0 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            color: white;
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            height: 3.2rem;
            padding: 0 1rem;
            background: rgba(15, 20, 25, 0.6);
            border: 1px solid rgba(31, 116, 231, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            background: rgba(15, 20, 25, 0.8);
            border-color: #1f74e7;
            box-shadow: 0 0 0 2px rgba(31, 116, 231, 0.2);
        }

        textarea.form-control {
            height: auto;
            min-height: 120px;
            resize: vertical;
            padding: 1rem;
        }

        /* Message Styles */
        .message {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .message.success {
            background: rgba(32, 201, 151, 0.1);
            border: 1px solid rgba(32, 201, 151, 0.2);
            color: #20c997;
        }

        .message.error {
            background: rgba(231, 69, 69, 0.1);
            border: 1px solid rgba(231, 69, 69, 0.2);
            color: #ff6b6b;
        }

        /* Submit Button */
        .submit-btn {
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(31, 116, 231, 0.4);
        }

        /* Contact Info */
        .contact-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(31, 116, 231, 0.1);
        }

        .contact-info h3 {
            color: white;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: rgba(31, 116, 231, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(31, 116, 231, 0.1);
        }

        .contact-item i {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(31, 116, 231, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f74e7;
            font-size: 1.1rem;
        }

        .contact-item-content h4 {
            color: white;
            font-size: 1rem;
            margin: 0 0 0.25rem 0;
        }

        .contact-item-content p {
            color: #8a9ba8;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
                padding: 1.5rem 0.75rem;
            }

            .sidebar-logo span,
            .menu-item span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
            }

            .menu-item {
                justify-content: center;
                padding: 1rem 0;
            }

            .menu-item i {
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            .contact-card {
                padding: 1.5rem;
                margin: 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }
        }

        /* For non-logged in users */
        .no-sidebar {
            margin-left: 0;
        }

        .no-sidebar .main-content {
            margin-left: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container <?= !$isLoggedIn ? 'no-sidebar' : '' ?>">
        <?php if ($isLoggedIn): ?>
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fa-solid fa-brain"></i>
                <span>SecondBrain</span>
            </div>

            <div class="sidebar-user">
                <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'logo.png' ?>" alt="Profile" class="user-avatar">
            </div>

            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="add_reminder.php" class="menu-item">
                    <i class="fas fa-plus"></i>
                    <span>Add Reminder</span>
                </a>
                <a href="view_reminder.php" class="menu-item">
                    <i class="fas fa-list"></i>
                    <span>Reminders</span>
                </a>
                <a href="my_complaints.php" class="menu-item">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <a href="profile.php" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-envelope"></i>
                    Contact Us
                </h1>
                <p class="page-subtitle">Send us your message, feedback, or complaint</p>
            </div>

            <div class="contact-card">
                <?php if (!empty($message)): ?>
                    <div class="message <?= strpos($message, 'Thank you') !== false ? 'success' : 'error' ?>">
                        <i class="fas <?= strpos($message, 'Thank you') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="contact.php" autocomplete="off">
                    <div class="form-group">
                        <label class="form-label" for="name">Your Name</label>
                        <input type="text" id="name" name="name" class="form-control" required maxlength="255" placeholder="Enter your full name" value="<?= $isLoggedIn ? htmlspecialchars($user['name']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Your Email</label>
                        <input type="email" id="email" name="email" class="form-control" required maxlength="255" placeholder="Enter your email address" value="<?= $isLoggedIn ? htmlspecialchars($_SESSION['email']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="message">Your Message</label>
                        <textarea id="message" name="message" class="form-control" required rows="5" maxlength="1000" placeholder="Tell us about your message, feedback, or complaint..."></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </button>
                </form>

                <div class="contact-info">
                    <h3>
                        <i class="fas fa-info-circle"></i>
                        Get in Touch
                    </h3>
                    
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-item-content">
                            <h4>Email Support</h4>
                            <p>secondsbrain@gmail.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div class="contact-item-content">
                            <h4>Response Time</h4>
                            <p>We typically respond within 24 hours</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-shield-alt"></i>
                        <div class="contact-item-content">
                            <h4>Privacy</h4>
                            <p>Your information is secure and confidential</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 