<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION["email"];

// Fetch user's name, profile picture, and is_admin
$stmt = $conn->prepare("SELECT name, profile_picture, is_admin FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Count total reminders
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM reminders WHERE user_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$total_reminders = $result->fetch_assoc()['total'];

// Count today's reminders
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) as today FROM reminders WHERE user_email = ? AND reminder_date = ?");
$stmt->bind_param("ss", $email, $today);
$stmt->execute();
$result = $stmt->get_result();
$today_reminders = $result->fetch_assoc()['today'];

// Count upcoming reminders (next 7 days)
$next_week = date('Y-m-d', strtotime('+7 days'));
$stmt = $conn->prepare("SELECT COUNT(*) as upcoming FROM reminders WHERE user_email = ? AND reminder_date BETWEEN ? AND ?");
$stmt->bind_param("sss", $email, $today, $next_week);
$stmt->execute();
$result = $stmt->get_result();
$upcoming_reminders = $result->fetch_assoc()['upcoming'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Dashboard | SecondBrain</title>
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

        .user-info h3 {
            color: white;
            font-size: 1rem;
            margin: 0;
        }

        .user-info p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin: 0;
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
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(35, 43, 62, 0.75);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(31, 116, 231, 0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(31, 116, 231, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.blue {
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
        }

        .stat-icon.green {
            background: rgba(32, 201, 151, 0.1);
            color: #20c997;
        }

        .stat-icon.orange {
            background: rgba(252, 185, 44, 0.1);
            color: #fcb92c;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .action-card {
            background: rgba(35, 43, 62, 0.75);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(31, 116, 231, 0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(31, 116, 231, 0.1);
            border-color: rgba(31, 116, 231, 0.3);
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(31, 116, 231, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #1f74e7;
        }

        .action-content h3 {
            color: white;
            font-size: 1.1rem;
            margin: 0 0 0.25rem 0;
        }

        .action-content p {
            color: #cfd8e3;
            font-size: 0.95rem;
            margin: 0;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
                padding: 1.5rem 0.75rem;
            }

            .sidebar-logo span,
            .user-info,
            .menu-item span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
            }

            .sidebar-user {
                padding: 0.5rem;
                justify-content: center;
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
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
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
                <div class="user-info">
                    <h3><?= htmlspecialchars($user['name']) ?></h3>
                   
                </div>
            </div>

            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="add_reminder.php" class="menu-item">
                    <i class="fas fa-plus"></i>
                    <span>Add Reminder</span>
                </a>
                <a href="view_reminder.php" class="menu-item">
                    <i class="fas fa-list"></i>
                    <span>View Reminders</span>
                </a>
                <a href="my_complaints.php" class="menu-item">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <a href="support_dashboard.php" class="menu-item">
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
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back, <?= htmlspecialchars($user['name']) ?>! Here's your reminder overview.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-value"><?= $total_reminders ?></div>
                    <div class="stat-label">Total Reminders</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-value"><?= $today_reminders ?></div>
                    <div class="stat-label">Today's Reminders</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-value"><?= $upcoming_reminders ?></div>
                    <div class="stat-label">Upcoming (Next 7 Days)</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="add_reminder.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="action-content">
                        <h3>Add New Reminder</h3>
                        <p>Create a new reminder with voice notes</p>
                    </div>
                </a>

                <a href="view_reminder.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="action-content">
                        <h3>View Reminders</h3>
                        <p>Manage your existing reminders</p>
                    </div>
                </a>

                <a href="profile.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="action-content">
                        <h3>Profile Settings</h3>
                        <p>Update your profile information</p>
                    </div>
                </a>

                <a href="my_complaints.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="action-content">
                        <h3>Messages</h3>
                        <p>View your messages and complaints</p>
                    </div>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
