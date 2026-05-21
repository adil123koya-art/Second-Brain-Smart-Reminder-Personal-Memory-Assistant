<?php
session_start();
include('db.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$message = "";

// Fetch user's name and profile picture
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultUser = $stmt->get_result();
$user = $resultUser->fetch_assoc();

// Fetch user's reminders for selection
$stmt = $conn->prepare("SELECT reminder_id, title, reminder_date, reminder_time FROM reminders WHERE user_email = ? ORDER BY reminder_date DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$reminders = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reminder_id = $_POST["reminder_id"];
    $issue_type = $_POST["issue_type"];
    $description = $_POST["description"];
    $consent = isset($_POST["consent"]) ? 1 : 0;
    
    if (!$consent) {
        $message = "<div class='message error'>❌ You must give consent for admin to view your reminder to submit a support request.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO support_requests (user_email, reminder_id, issue_type, description, status, created_at) VALUES (?, ?, ?, ?, 'new', NOW())");
        $stmt->bind_param("siss", $email, $reminder_id, $issue_type, $description);
        
        if ($stmt->execute()) {
            $message = "<div class='message success'>✅ Support request submitted successfully! We'll review it and get back to you soon.</div>";
        } else {
            $message = "<div class='message error'>❌ Error submitting request. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Support Request | SecondBrain</title>
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

        /* Support Form Card */
        .support-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2.5rem;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .support-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
            border-radius: 24px 24px 0 0;
        }

        /* Form Styles */
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

        select.form-control {
            cursor: pointer;
        }

        textarea.form-control {
            height: auto;
            min-height: 120px;
            resize: vertical;
            padding: 1rem;
        }

        /* Consent Section */
        .consent-section {
            background: rgba(31, 116, 231, 0.05);
            border: 1px solid rgba(31, 116, 231, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .consent-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .consent-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 0.2rem;
            accent-color: #1f74e7;
        }

        .consent-text {
            color: #8a9ba8;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .consent-text strong {
            color: #1f74e7;
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
            .support-card {
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
                <a href="support_request.php" class="menu-item active">
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
                    Support Request
                </h1>
                <p class="page-subtitle">Get help with your reminders and account issues</p>
            </div>

            <div class="support-card">
                <?php if (!empty($message)): ?>
                    <div class="message <?= strpos($message, "success") !== false ? 'success' : 'error' ?>">
                        <i class="fas <?= strpos($message, "success") !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                        <?= str_replace(['<div class="message success">', '<div class="message error">', '</div>'], '', $message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="form-group">
                        <label class="form-label" for="reminder_id">Select Reminder (if applicable)</label>
                        <select id="reminder_id" name="reminder_id" class="form-control" required>
                            <option value="">Choose a reminder...</option>
                            <?php while ($reminder = $reminders->fetch_assoc()): ?>
                                <option value="<?= $reminder['reminder_id'] ?>">
                                    <?= htmlspecialchars($reminder['title']) ?> - <?= $reminder['reminder_date'] ?> at <?= $reminder['reminder_time'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="issue_type">Issue Type</label>
                        <select id="issue_type" name="issue_type" class="form-control" required>
                            <option value="">Select issue type...</option>
                            <option value="reminder_not_working">Reminder not working</option>
                            <option value="voice_note_issue">Voice note issue</option>
                            <option value="wrong_date_time">Wrong date/time</option>
                            <option value="cant_edit">Can't edit reminder</option>
                            <option value="cant_delete">Can't delete reminder</option>
                            <option value="other">Other issue</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Describe the Issue</label>
                        <textarea id="description" name="description" class="form-control" required rows="5" placeholder="Please describe the issue you're experiencing in detail..."></textarea>
                    </div>

                    <div class="consent-section">
                        <div class="consent-checkbox">
                            <input type="checkbox" id="consent" name="consent" required>
                            <div class="consent-text">
                                <strong>I give consent for the admin to view my reminder data</strong> to help resolve this issue. 
                                This is required for technical support. Your data will only be accessed for this specific support request and will be handled securely.
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Support Request
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 