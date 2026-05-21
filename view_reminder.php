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

$query = $conn->prepare("SELECT * FROM reminders WHERE user_email = ? ORDER BY reminder_date, reminder_time");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Reminders | SecondBrain</title>
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

        /* Reminders Grid */
        .reminders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .reminder-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .reminder-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 116, 231, 0.15);
            border-color: rgba(31, 116, 231, 0.3);
        }

        .reminder-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .card-icon {
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

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: white;
            margin: 0;
            flex: 1;
        }

        .card-desc {
            color: #cfd8e3;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            background: rgba(15, 20, 25, 0.3);
            padding: 1rem;
            border-radius: 12px;
            border-left: 3px solid #1f74e7;
        }

        .card-date {
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-date i {
            font-size: 1rem;
        }

        .card-created {
            color: #8a9ba8;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 1.5rem;
            padding: 0.5rem;
            background: rgba(15, 20, 25, 0.2);
            border-radius: 8px;
        }

        .card-actions {
            display: flex;
            gap: 0.75rem;
        }

        .edit-btn, .delete-btn {
            flex: 1;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .edit-btn {
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
            border: 1px solid rgba(31, 116, 231, 0.2);
        }

        .edit-btn:hover {
            background: #1f74e7;
            color: white;
            transform: translateY(-2px);
        }

        .delete-btn {
            background: rgba(231, 69, 69, 0.1);
            color: #ff6b6b;
            border: 1px solid rgba(231, 69, 69, 0.2);
        }

        .delete-btn:hover {
            background: #ff6b6b;
            color: white;
            transform: translateY(-2px);
        }

        /* Audio Player Styling */
        .audio-container {
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(15, 20, 25, 0.4);
            border-radius: 12px;
            border: 1px solid rgba(31, 116, 231, 0.1);
        }

        .audio-container audio {
            width: 100%;
            border-radius: 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #8a9ba8;
        }

        .empty-state i {
            font-size: 4rem;
            color: #1f74e7;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .add-first-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .add-first-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(31, 116, 231, 0.4);
        }

        /* Status Badges */
        .card-status {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-badge.sent {
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
            border: 1px solid rgba(31, 116, 231, 0.2);
        }

        .status-badge.received {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .status-badge.seen {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.2);
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
            .reminders-grid {
                grid-template-columns: 1fr;
            }

            .card-actions {
                flex-direction: column;
            }

            .main-content {
                padding: 1rem;
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
                <a href="view_reminder.php" class="menu-item active">
                    <i class="fas fa-list"></i>
                    <span>Reminders</span>
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
                <h1 class="page-title">
                    <i class="fas fa-list"></i>
                    Reminders
                </h1>
                <p class="page-subtitle">Manage and view all your reminders</p>
            </div>

            <div class="reminders-grid">
                <?php
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                    <div class="reminder-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <h3 class="card-title"><?= htmlspecialchars($row['title']) ?></h3>
                        </div>
                        
                        <div class="card-desc"><?= htmlspecialchars($row['description']) ?></div>
                        
                        <?php if (!empty($row['voice_file']) && file_exists($row['voice_file'])): ?>
                            <div class="audio-container">
                                <audio controls src="<?= $row['voice_file'] ?>"></audio>
                            </div>
                        <?php endif; ?>
                        
                    <div class="card-date">
                        <i class="fas fa-calendar-alt"></i>
                        <strong><?= $row['reminder_date'] ?> at <?= $row['reminder_time'] ?></strong>
                    </div>

                    <div class="card-status">
                        <?php if ($row['is_sent']): ?>
                            <span class="status-badge sent">Sent</span>
                        <?php endif; ?>
                        <?php if ($row['is_received']): ?>
                            <span class="status-badge received">Received</span>
                        <?php endif; ?>
                        <?php if (isset($row['is_seen']) && $row['is_seen']): ?>
                            <span class="status-badge seen">Seen</span>
                        <?php endif; ?>
                    </div>
                        
                        <div class="card-created">
                            Created: <?= isset($row['created_at']) ? date("d M Y, h:i A", strtotime($row['created_at'])) : '' ?>
                        </div>
                        
                        <div class="card-actions">
                            <a class="edit-btn" href="edit_reminder.php?id=<?= $row['reminder_id'] ?>">
                                <i class="fas fa-edit"></i>
                                Edit
                            </a>
                            <a class="delete-btn" href="delete_reminder.php?id=<?= $row['reminder_id'] ?>" onclick="return confirm('Are you sure you want to delete this reminder?')">
                                <i class="fas fa-trash"></i>
                                Delete
                            </a>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <i class="fas fa-bell-slash"></i>
                        <h3>No Reminders Yet</h3>
                        <p>You haven't created any reminders yet. Start by adding your first reminder!</p>
                        <a href="add_reminder.php" class="add-first-btn">
                            <i class="fas fa-plus"></i>
                            Add Your First Reminder
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>