<?php
session_start();
include('db.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch user's name and profile picture
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultUser = $stmt->get_result();
$user = $resultUser->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Support | SecondBrain</title>
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

        /* Support Options Grid */
        .support-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .support-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .support-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(31, 116, 231, 0.15);
            border-color: rgba(31, 116, 231, 0.3);
        }

        .support-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
        }

        .support-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
        }

        .support-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            text-align: center;
            margin-bottom: 1rem;
        }

        .support-description {
            color: #8a9ba8;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .support-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .support-features li {
            color: #cfd8e3;
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .support-features li i {
            color: #1f74e7;
            font-size: 0.9rem;
        }

        .support-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #1f74e7;
            font-weight: 600;
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(31, 116, 231, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .support-card:hover .support-action {
            background: rgba(31, 116, 231, 0.2);
            color: white;
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
            .support-options {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .support-card {
                padding: 2rem;
            }

            .main-content {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
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
                <a href="support_dashboard.php" class="menu-item active">
                    <i class="fas fa-headset"></i>
                    <span>Support</span>
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

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-headset"></i>
                    Support Center
                </h1>
                <p class="page-subtitle">Get help with your account and reminders</p>
            </div>

            <div class="support-options">
                <!-- Contact Us Card -->
                <a href="contact_full.php" class="support-card">
                    <div class="support-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="support-title">Contact Us</h3>
                    <p class="support-description">
                        Send us a general message, feedback, or complaint about our service.
                    </p>
                    <ul class="support-features">
                        <li><i class="fas fa-check"></i> General inquiries and feedback</li>
                        <li><i class="fas fa-check"></i> Service complaints</li>
                        <li><i class="fas fa-check"></i> Feature requests</li>
                        <li><i class="fas fa-check"></i> Account-related issues</li>
                    </ul>
                    <div class="support-action">
                        <i class="fas fa-arrow-right"></i>
                        Send Message
                    </div>
                </a>

                <!-- Support Request Card -->
                <a href="support_request.php" class="support-card">
                    <div class="support-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="support-title">Support Request</h3>
                    <p class="support-description">
                        Get technical help with specific reminder issues. Our team can access your reminder data to assist you.
                    </p>
                    <ul class="support-features">
                        <li><i class="fas fa-check"></i> Reminder not working</li>
                        <li><i class="fas fa-check"></i> Voice note issues</li>
                        <li><i class="fas fa-check"></i> Technical problems</li>
                        <li><i class="fas fa-check"></i> Data access with consent</li>
                    </ul>
                    <div class="support-action">
                        <i class="fas fa-arrow-right"></i>
                        Submit Request
                    </div>
                </a>
            </div>
        </main>
    </div>
</body>
</html> 