<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if (!isset($_GET['complaint_id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$complaint_id = $_GET['complaint_id'];

/* ---------------------------------------------------------
    STEP 1 — FETCH COMPLAINT FIRST (IMPORTANT)
--------------------------------------------------------- */
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id = ?");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();
$complaint = $result->fetch_assoc();

if (!$complaint) {
    header("Location: admin_dashboard.php");
    exit;
}

/* ---------------------------------------------------------
    STEP 2 — HANDLE FORM SUBMISSION
--------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $status = $_POST["status"];
    $admin_response = $_POST["admin_response"] ?? '';

    // Update complaint status
    $stmt = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $complaint_id);

    if ($stmt->execute()) {

        // Create notification message
        $notification_title = "Complaint Status Updated";
        $notification_message = "Your complaint status is now: " . ucfirst(str_replace('_', ' ', $status));

        if (!empty($admin_response)) {
            $notification_message .= ". Admin Response: " . $admin_response;
        }

        // Insert notification
        $stmt2 = $conn->prepare("INSERT INTO notifications 
            (user_email, title, message, type, related_id)
            VALUES (?, ?, ?, 'complaint_response', ?)");

        $stmt2->bind_param(
            "sssi",
            $complaint['email'],       // USER EMAIL NOW AVAILABLE
            $notification_title,
            $notification_message,
            $complaint_id              // RELATED COMPLAINT ID
        );

        $stmt2->execute();

        $message = "<div class='message success'>Status updated successfully! User has been notified.</div>";

    } else {
        $message = "<div class='message error'>Error updating status.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="logo.png">
    <title>Update Complaint Status | Admin Dashboard | Second Brain</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #181F2A;
            color: #F5F7FA;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #232B3E;
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(24,31,42,0.12);
            padding: 36px 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 36px;
        }
        .header h1 {
            color: #1f74e7;
            font-size: 2.2rem;
            margin-bottom: 8px;
        }
        .complaint-info {
            background: #283046;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .complaint-info p {
            margin: 8px 0;
        }
        .complaint-info strong {
            color: #1f74e7;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1f74e7;
            font-weight: 600;
        }
        .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #3a4a6b;
            background: #1a2235;
            color: #F5F7FA;
            font-size: 16px;
        }
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #3a4a6b;
            background: #1a2235;
            color: #F5F7FA;
            font-size: 16px;
        }
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .message.success {
            background: rgba(32, 201, 151, 0.2);
            color: #20C997;
            border: 1px solid rgba(32, 201, 151, 0.3);
        }
        .message.error {
            background: rgba(231, 69, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(231, 69, 69, 0.3);
        }
        .actions {
            text-align: center;
            margin-top: 32px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 8px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary { background: #1f74e7; color: white; }
        .btn-success { background: #20C997; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-primary:hover { background: #1455b8; }
        .btn-success:hover { background: #169c74; }
        .btn-secondary:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Update Complaint Status</h1>
        </div>
        
        <?php if (!empty($message)) echo $message; ?>
        
        <div class="complaint-info">
            <p><strong>From:</strong> <?= htmlspecialchars($complaint['name']) ?> (<?= htmlspecialchars($complaint['email']) ?>)</p>
            <p><strong>Date:</strong> <?= date('F d, Y \a\t g:i A', strtotime($complaint['created_at'])) ?></p>
            <p><strong>Current Status:</strong> 
                <span style="background: <?= $complaint['status'] == 'new' ? '#ff6b6b' : ($complaint['status'] == 'in_progress' ? '#feca57' : '#20C997') ?>; color: <?= $complaint['status'] == 'in_progress' ? '#2c3e50' : 'white' ?>; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                    <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
                </span>
            </p>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="status">Update Status:</label>
                <select name="status" id="status" required>
                    <option value="new" <?= $complaint['status'] == 'new' ? 'selected' : '' ?>>New</option>
                    <option value="in_progress" <?= $complaint['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="resolved" <?= $complaint['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="admin_response">Admin Response (Optional):</label>
                <textarea name="admin_response" id="admin_response" rows="4" placeholder="Add your response or explanation..."></textarea>
            </div>
            
            <div class="actions">
                <button type="submit" class="btn btn-success">Update Status</button>
                <a href="view_complaint.php?id=<?= $complaint['id'] ?>" class="btn btn-primary">View Details</a>
                <a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</body>
</html>
