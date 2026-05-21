<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}

$message = "";

// Handle status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    
    if ($_POST['action'] == 'update_status') {
        $status = $_POST['status'];
        $admin_response = $_POST['admin_response'] ?? '';
        
        // Get the current support request details
        $stmt = $conn->prepare("SELECT user_email, issue_type FROM support_requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $support_result = $stmt->get_result();
        $support_request = $support_result->fetch_assoc();
        
        $stmt = $conn->prepare("UPDATE support_requests SET status = ?, admin_response = ?, updated_at = NOW() WHERE request_id = ?");
        $stmt->bind_param("ssi", $status, $admin_response, $request_id);
        
        if ($stmt->execute()) {
            // Create notification for the user
            if ($support_request && !empty($admin_response)) {
                $notification_title = "Support Request Update";
                $notification_message = "Your support request regarding '" . ucwords(str_replace('_', ' ', $support_request['issue_type'])) . "' has been updated. Status: " . ucfirst(str_replace('_', ' ', $status));
                
                $stmt = $conn->prepare("INSERT INTO notifications (user_email, title, message, type, related_id) VALUES (?, ?, ?, 'support_response', ?)");
                $stmt->bind_param("sssi", $support_request['user_email'], $notification_title, $notification_message, $request_id);
                $stmt->execute();
            }
            
            $message = "<div class='message success'>✅ Support request updated successfully!</div>";
        } else {
            $message = "<div class='message error'>❌ Error updating request.</div>";
        }
    }
}

// Fetch support requests with user and reminder details
$query = "
    SELECT 
        sr.*,
        u.name as user_name,
        r.title as reminder_title,
        r.description as reminder_description,
        r.reminder_date,
        r.reminder_time,
        r.voice_file
    FROM support_requests sr
    LEFT JOIN users u ON sr.user_email = u.email
    LEFT JOIN reminders r ON sr.reminder_id = r.reminder_id
    ORDER BY sr.created_at DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Support Requests | Admin | SecondBrain</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #181F2A 0%, #232B3E 100%);
            color: #F5F7FA;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .admin-header {
            background: #232B3E;
            padding: 32px 0 18px 0;
            text-align: center;
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(24,31,42,0.10);
            margin-bottom: 36px;
            position: relative;
        }

        .admin-header h1 {
            color: #1f74e7;
            font-size: 2.2rem;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .admin-logout-btn {
            position: absolute;
            top: 24px;
            right: 36px;
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
            color: #fff;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(31,116,231,0.10);
            transition: background 0.2s, box-shadow 0.2s;
        }

        .admin-logout-btn:hover {
            background: #1455b8;
            box-shadow: 0 4px 16px rgba(31,116,231,0.18);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto 40px auto;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 18px;
            color: #AAB4C3;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s;
            font-weight: 600;
        }

        .back-link:hover {
            color: #1f74e7;
        }

        .message {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            text-align: center;
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

        .support-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2rem;
            margin-bottom: 1.5rem;
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
            border-radius: 20px 20px 0 0;
        }

        .support-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(31, 116, 231, 0.1);
        }

        .support-info h3 {
            color: #1f74e7;
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
        }

        .support-meta {
            color: #8a9ba8;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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

        .status-closed { 
            background: rgba(108, 117, 125, 0.1); 
            color: #6c757d; 
            border: 1px solid rgba(108, 117, 125, 0.2);
        }

        .reminder-details {
            background: rgba(31, 116, 231, 0.05);
            border: 1px solid rgba(31, 116, 231, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
        }

        .reminder-details h4 {
            color: #1f74e7;
            margin: 0 0 1rem 0;
            font-size: 1.1rem;
        }

        .reminder-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .reminder-info p {
            margin: 0;
            color: #8a9ba8;
        }

        .reminder-info strong {
            color: white;
        }

        .issue-description {
            background: rgba(15, 20, 25, 0.3);
            border-radius: 12px;
            padding: 1rem;
            margin: 1rem 0;
            border-left: 3px solid #1f74e7;
        }

        .issue-description h4 {
            color: #1f74e7;
            margin: 0 0 0.5rem 0;
        }

        .issue-description p {
            color: #cfd8e3;
            margin: 0;
            line-height: 1.6;
        }

        .admin-response-form {
            background: rgba(15, 20, 25, 0.4);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .admin-response-form h4 {
            color: #1f74e7;
            margin: 0 0 1rem 0;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            color: white;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: rgba(15, 20, 25, 0.6);
            border: 1px solid rgba(31, 116, 231, 0.2);
            border-radius: 8px;
            color: white;
            font-size: 0.9rem;
        }

        .form-control:focus {
            outline: none;
            border-color: #1f74e7;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            background: #1f74e7;
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #1455b8;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .audio-player {
            margin-top: 1rem;
        }

        .audio-player audio {
            width: 100%;
            border-radius: 8px;
        }

        .no-requests {
            text-align: center;
            padding: 4rem 2rem;
            color: #8a9ba8;
        }

        .no-requests i {
            font-size: 4rem;
            color: #1f74e7;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .no-requests h3 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .support-header {
                flex-direction: column;
                gap: 1rem;
            }

            .reminder-info {
                grid-template-columns: 1fr;
            }

            .admin-header {
                padding: 24px 0 18px 0;
            }

            .admin-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <a href="logout.php" class="admin-logout-btn">Logout</a>
        <h1>Support Requests</h1>
        <p style="color: #AAB4C3; margin: 0;">Manage user support requests with secure access</p>
    </div>

    <div class="container">
        <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        
        <?php if (!empty($message)) echo $message; ?>

        <?php if ($result->num_rows === 0): ?>
            <div class="no-requests">
                <i class="fas fa-headset"></i>
                <h3>No Support Requests</h3>
                <p>No support requests have been submitted yet.</p>
            </div>
        <?php else: ?>
            <?php while ($request = $result->fetch_assoc()): ?>
                <div class="support-card">
                    <div class="support-header">
                        <div class="support-info">
                            <h3>Support Request #<?= $request['request_id'] ?></h3>
                            <div class="support-meta">
                                <strong>User:</strong> <?= htmlspecialchars($request['user_name']) ?> (<?= htmlspecialchars($request['user_email']) ?>)<br>
                                <strong>Submitted:</strong> <?= date('M d, Y, h:i A', strtotime($request['created_at'])) ?><br>
                                <strong>Issue Type:</strong> <?= ucwords(str_replace('_', ' ', $request['issue_type'])) ?>
                            </div>
                        </div>
                        <span class="status-badge status-<?= $request['status'] ?>">
                            <i class="fas fa-circle"></i>
                            <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                        </span>
                    </div>

                    <?php if ($request['reminder_id']): ?>
                        <div class="reminder-details">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <h4><i class="fas fa-bell"></i> Reminder Details (User Consent Given)</h4>
                                <a href="admin_edit_reminder.php?reminder_id=<?= $request['reminder_id'] ?>&support_request_id=<?= $request['request_id'] ?>" 
                                   class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                    <i class="fas fa-edit"></i>
                                    Edit Reminder
                                </a>
                            </div>
                            <div class="reminder-info">
                                <p><strong>Title:</strong> <?= htmlspecialchars($request['reminder_title']) ?></p>
                                <p><strong>Date:</strong> <?= $request['reminder_date'] ?> at <?= $request['reminder_time'] ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($request['reminder_description']) ?></p>
                            </div>
                            <?php if (!empty($request['voice_file']) && file_exists($request['voice_file'])): ?>
                                <div class="audio-player">
                                    <strong>Voice Note:</strong>
                                    <audio controls src="<?= $request['voice_file'] ?>"></audio>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="issue-description">
                        <h4><i class="fas fa-exclamation-triangle"></i> Issue Description</h4>
                        <p><?= nl2br(htmlspecialchars($request['description'])) ?></p>
                    </div>

                    <?php if (!empty($request['admin_response'])): ?>
                        <div class="issue-description">
                            <h4><i class="fas fa-reply"></i> Admin Response</h4>
                            <p><?= nl2br(htmlspecialchars($request['admin_response'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="admin-response-form">
                        <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                        <input type="hidden" name="action" value="update_status">
                        
                        <div class="form-group">
                            <label class="form-label">Update Status</label>
                            <select name="status" class="form-control" required>
                                <option value="new" <?= $request['status'] == 'new' ? 'selected' : '' ?>>New</option>
                                <option value="in_progress" <?= $request['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="resolved" <?= $request['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                <option value="closed" <?= $request['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Admin Response (Optional)</label>
                            <textarea name="admin_response" class="form-control" placeholder="Add your response or solution..."><?= htmlspecialchars($request['admin_response'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn">Update Request</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>
</html> 