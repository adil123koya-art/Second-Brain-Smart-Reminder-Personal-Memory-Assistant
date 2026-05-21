<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION["email"];

// Fetch user's name and profile picture
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultUser = $stmt->get_result();
$user = $resultUser->fetch_assoc();

// Fetch complaints for this user
$stmt = $conn->prepare("SELECT * FROM complaints WHERE email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$complaints_result = $stmt->get_result();
$complaints = $complaints_result->fetch_all(MYSQLI_ASSOC);

// Fetch support requests for this user
$stmt = $conn->prepare("SELECT * FROM support_requests WHERE user_email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$support_result = $stmt->get_result();
$support_requests = $support_result->fetch_all(MYSQLI_ASSOC);

// Fetch notifications for this user
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$notifications_result = $stmt->get_result();
$notifications = $notifications_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>My Complaints | SecondBrain</title>
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

        /* Send Message Button */
        .send-message-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .send-message-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(31, 116, 231, 0.4);
        }

        /* Complaints Table Card */
        .complaints-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2rem;
            overflow: hidden;
        }

        .complaints-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            color: #F5F7FA;
            border-radius: 12px;
            overflow: hidden;
        }

        .complaints-table th {
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
            font-weight: 600;
            padding: 1.2rem 1rem;
            text-align: left;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(31, 116, 231, 0.2);
        }

        .complaints-table td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid rgba(31, 116, 231, 0.1);
            vertical-align: top;
        }

        .complaints-table tr {
            transition: all 0.3s ease;
        }

        .complaints-table tr:hover {
            background: rgba(31, 116, 231, 0.05);
        }

        .complaints-table tr:last-child td {
            border-bottom: none;
        }

        .message-cell {
            max-width: 400px;
            word-wrap: break-word;
            line-height: 1.5;
        }

        .date-cell {
            white-space: nowrap;
            color: #8a9ba8;
            font-size: 0.9rem;
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: 0.5px;
        }

        .status-badge i {
            font-size: 0.7rem;
        }

        .status-new { 
            background: rgba(255, 107, 107, 0.1); 
            color: #ff6b6b; 
            border: 1px solid rgba(255, 107, 107, 0.2);
        }

        .status-in_progress { 
            background: rgba(252, 185, 44, 0.1); 
            color: #fcb92c; 
            border: 1px solid rgba(252, 185, 44, 0.2);
        }

        .status-resolved { 
            background: rgba(32, 201, 151, 0.1); 
            color: #20c997; 
            border: 1px solid rgba(32, 201, 151, 0.2);
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

        .contact-btn {
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

        .contact-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(31, 116, 231, 0.4);
        }

        /* Stats Summary */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(35, 43, 62, 0.75);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(31, 116, 231, 0.1);
            text-align: center;
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
            margin: 0 auto 1rem;
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

        /* Tabs for different message types */
        .message-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(31, 116, 231, 0.1);
            padding-bottom: 1rem;
        }

        .tab-button {
            background: none;
            border: none;
            color: var(--text-light);
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-button.active {
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
        }

        .tab-button:hover {
            background: rgba(31, 116, 231, 0.05);
            color: #1f74e7;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Support request styles */
        .support-request {
            background: rgba(31, 116, 231, 0.05);
            border: 1px solid rgba(31, 116, 231, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .support-request h4 {
            color: #1f74e7;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .support-response {
            background: rgba(32, 201, 151, 0.05);
            border: 1px solid rgba(32, 201, 151, 0.2);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .support-response h5 {
            color: #20c997;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        /* Notification styles */
        .notification-item {
            background: rgba(252, 185, 44, 0.05);
            border: 1px solid rgba(252, 185, 44, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .notification-item.unread {
            background: rgba(252, 185, 44, 0.1);
            border-color: rgba(252, 185, 44, 0.4);
        }

        .notification-title {
            color: #fcb92c;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .notification-message {
            color: var(--text-light);
            line-height: 1.5;
        }

        .notification-date {
            color: #8a9ba8;
            font-size: 0.9rem;
            margin-top: 0.5rem;
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
            .stats-summary {
                grid-template-columns: 1fr;
            }

            .complaints-table {
                font-size: 0.9rem;
            }

            .complaints-table th,
            .complaints-table td {
                padding: 0.8rem 0.5rem;
            }

            .message-cell {
                max-width: 200px;
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
                <a href="view_reminder.php" class="menu-item">
                    <i class="fas fa-list"></i>
                    <span>Reminders</span>
                </a>
                <a href="my_complaints.php" class="menu-item active">
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
                <h1 class="page-title">
                    <i class="fas fa-envelope"></i>
                    My Messages & Complaints
                </h1>
                <p class="page-subtitle">Track the status of your submitted messages and complaints</p>
            </div>

            <?php
            // Calculate stats
            $total_complaints = count($complaints);
            $total_support = count($support_requests);
            $total_notifications = count($notifications);
            $unread_notifications = count(array_filter($notifications, function($n) { return !$n['is_read']; }));
            
            $resolved_complaints = count(array_filter($complaints, function($c) { return $c['status'] === 'resolved'; }));
            $in_progress_complaints = count(array_filter($complaints, function($c) { return $c['status'] === 'in_progress'; }));
            $new_complaints = count(array_filter($complaints, function($c) { return $c['status'] === 'new'; }));
            
            $resolved_support = count(array_filter($support_requests, function($s) { return $s['status'] === 'resolved'; }));
            $in_progress_support = count(array_filter($support_requests, function($s) { return $s['status'] === 'in_progress'; }));
            $new_support = count(array_filter($support_requests, function($s) { return $s['status'] === 'new'; }));
            ?>

            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-value"><?= $total_complaints + $total_support ?></div>
                    <div class="stat-label">Total Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="stat-value"><?= $total_support ?></div>
                    <div class="stat-label">Support Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-value"><?= $unread_notifications ?></div>
                    <div class="stat-label">New Updates</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?= $resolved_complaints + $resolved_support ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>

            <!-- Messages and Support Dashboard -->
            <div class="complaints-card">
                <?php if (count($complaints) === 0 && count($support_requests) === 0 && count($notifications) === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Messages Yet</h3>
                        <p>You haven't submitted any messages or support requests yet.</p>
                        <a href="contact_full.php" class="send-message-btn">
                            <i class="fas fa-plus"></i>
                            Send New Message
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Tabs -->
                    <div class="message-tabs">
                        <button class="tab-button active" onclick="showTab('notifications')">
                            <i class="fas fa-bell"></i> Updates (<?= $unread_notifications ?>)
                        </button>
                        <button class="tab-button" onclick="showTab('complaints')">
                            <i class="fas fa-envelope"></i> Messages (<?= $total_complaints ?>)
                        </button>
                        <button class="tab-button" onclick="showTab('support')">
                            <i class="fas fa-headset"></i> Support (<?= $total_support ?>)
                        </button>
                    </div>

                    <!-- Notifications Tab -->
                    <div id="notifications" class="tab-content active">
                        <?php if (count($notifications) === 0): ?>
                            <div class="empty-state">
                                <i class="fas fa-bell"></i>
                                <h3>No Updates</h3>
                                <p>You don't have any notifications yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item <?= !$notification['is_read'] ? 'unread' : '' ?>" 
                                     data-notification-id="<?= $notification['notification_id'] ?>"
                                     onclick="markAsRead(<?= $notification['notification_id'] ?>, this)">
                                    <div class="notification-title">
                                        <i class="fas fa-info-circle"></i>
                                        <?= htmlspecialchars($notification['title']) ?>
                                    </div>
                                    <div class="notification-message">
                                        <?= htmlspecialchars($notification['message']) ?>
                                    </div>
                                    <div class="notification-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= date('M d, Y, h:i A', strtotime($notification['created_at'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Complaints Tab -->
                    <div id="complaints" class="tab-content">
                        <?php if (count($complaints) === 0): ?>
                            <div class="empty-state">
                                <i class="fas fa-envelope"></i>
                                <h3>No Messages</h3>
                                <p>You haven't submitted any messages yet.</p>
                                <a href="contact_full.php" class="send-message-btn">
                                    <i class="fas fa-plus"></i>
                                    Send New Message
                                </a>
                            </div>
                        <?php else: ?>
                            <table class="complaints-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($complaints as $complaint): ?>
                                    <tr>
                                        <td class="date-cell">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= date('M d, Y', strtotime($complaint['created_at'])) ?>
                                        </td>
                                        <td class="message-cell"><?= htmlspecialchars($complaint['message']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $complaint['status'] ?>">
                                                <?php
                                                $status_icons = [
                                                    'new' => 'fas fa-exclamation-circle',
                                                    'in_progress' => 'fas fa-clock',
                                                    'resolved' => 'fas fa-check-circle'
                                                ];
                                                $icon = $status_icons[$complaint['status']] ?? 'fas fa-circle';
                                                ?>
                                                <i class="<?= $icon ?>"></i>
                                                <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Support Requests Tab -->
                    <div id="support" class="tab-content">
                        <?php if (count($support_requests) === 0): ?>
                            <div class="empty-state">
                                <i class="fas fa-headset"></i>
                                <h3>No Support Requests</h3>
                                <p>You haven't submitted any support requests yet.</p>
                                <a href="support_request.php" class="send-message-btn">
                                    <i class="fas fa-plus"></i>
                                    Submit Support Request
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($support_requests as $request): ?>
                                <div class="support-request">
                                    <h4>
                                        <i class="fas fa-tools"></i>
                                        Support Request #<?= $request['request_id'] ?> - <?= ucwords(str_replace('_', ' ', $request['issue_type'])) ?>
                                    </h4>
                                    <p><strong>Submitted:</strong> <?= date('M d, Y, h:i A', strtotime($request['created_at'])) ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="status-badge status-<?= $request['status'] ?>">
                                            <?php
                                            $status_icons = [
                                                'new' => 'fas fa-exclamation-circle',
                                                'in_progress' => 'fas fa-clock',
                                                'resolved' => 'fas fa-check-circle',
                                                'closed' => 'fas fa-times-circle'
                                            ];
                                            $icon = $status_icons[$request['status']] ?? 'fas fa-circle';
                                            ?>
                                            <i class="<?= $icon ?>"></i>
                                            <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                        </span>
                                    </p>
                                    <p><strong>Issue:</strong> <?= htmlspecialchars($request['description']) ?></p>
                                    
                                    <?php if (!empty($request['admin_response'])): ?>
                                        <div class="support-response">
                                            <h5><i class="fas fa-reply"></i> Admin Response</h5>
                                            <p><?= nl2br(htmlspecialchars($request['admin_response'])) ?></p>
                                            <small style="color: #8a9ba8;">
                                                <i class="fas fa-calendar-alt"></i>
                                                Updated: <?= date('M d, Y, h:i A', strtotime($request['updated_at'])) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(31, 116, 231, 0.1);">
                        <a href="contact_full.php" class="send-message-btn" style="margin-right: 1rem;">
                            <i class="fas fa-envelope"></i>
                            Send Message
                        </a>
                        <a href="support_request.php" class="send-message-btn">
                            <i class="fas fa-headset"></i>
                            Support Request
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }

        function markAsRead(notificationId, element) {
            // Send AJAX request to mark notification as read
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove unread styling
                    element.classList.remove('unread');
                    
                    // Update the notification count in the tab button
                    const notificationsTab = document.querySelector('.tab-button[onclick*="notifications"]');
                    const currentText = notificationsTab.innerHTML;
                    const match = currentText.match(/Updates \((\d+)\)/);
                    if (match) {
                        const currentCount = parseInt(match[1]);
                        if (currentCount > 0) {
                            const newCount = currentCount - 1;
                            notificationsTab.innerHTML = currentText.replace(
                                /Updates \(\d+\)/,
                                `Updates (${newCount})`
                            );
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }
    </script>
</body>
</html> 