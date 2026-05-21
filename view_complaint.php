<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['complaint_id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$complaint_id = $_GET['complaint_id'];
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id = ?");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();
$complaint = $result->fetch_assoc();

if (!$complaint) {
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="logo.png">
    <title>View Complaint | Admin Dashboard | Second Brain</title>
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
            max-width: 800px;
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
        .complaint-details {
            background: #283046;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 16px;
            border-bottom: 1px solid #3a4a6b;
            padding-bottom: 12px;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #1f74e7;
            width: 120px;
            flex-shrink: 0;
        }
        .detail-value {
            flex: 1;
            color: #F5F7FA;
        }
        .message-content {
            background: #1a2235;
            border-radius: 8px;
            padding: 16px;
            margin-top: 8px;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-new { background: #ff6b6b; color: white; }
        .status-in_progress { background: #feca57; color: #2c3e50; }
        .status-resolved { background: #20C997; color: white; }
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
        }
        .btn-primary {
            background: #1f74e7;
            color: white;
        }
        .btn-primary:hover {
            background: #1455b8;
        }
        .btn-success {
            background: #20C997;
            color: white;
        }
        .btn-success:hover {
            background: #169c74;
        }
        .btn-danger {
            background: #e74545;
            color: white;
        }
        .btn-danger:hover {
            background: #b83232;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Complaint Details</h1>
        </div>
        
        <div class="complaint-details">
            <div class="detail-row">
                <div class="detail-label">Name:</div>
                <div class="detail-value"><?= htmlspecialchars($complaint['name']) ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value"><?= htmlspecialchars($complaint['email']) ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <span class="status-badge status-<?= $complaint['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
                    </span>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Date:</div>
                <div class="detail-value"><?= date('F d, Y \a\t g:i A', strtotime($complaint['created_at'])) ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Message:</div>
                <div class="detail-value">
                    <div class="message-content"><?= htmlspecialchars($complaint['message']) ?></div>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <a href="update_complaint_status.php?id=<?= $complaint['id'] ?>" class="btn btn-success">Update Status</a>
            <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            <a href="delete_complaint.php?id=<?= $complaint['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this complaint?')">Delete</a>
        </div>
    </div>
</body>
</html> 