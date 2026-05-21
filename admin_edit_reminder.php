<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}

$message = "";
$reminder = null;

// Get reminder ID from URL
$reminder_id = $_GET['reminder_id'] ?? null;
$support_request_id = $_GET['support_request_id'] ?? null;

if (!$reminder_id) {
    header("Location: admin_support.php");
    exit;
}

// Fetch reminder details
$stmt = $conn->prepare("
    SELECT r.*, u.name as user_name, u.email as user_email 
    FROM reminders r 
    JOIN users u ON r.user_email = u.email 
    WHERE r.reminder_id = ?
");
$stmt->bind_param("i", $reminder_id);
$stmt->execute();
$result = $stmt->get_result();
$reminder = $result->fetch_assoc();

if (!$reminder) {
    header("Location: admin_support.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $reminder_date = $_POST['reminder_date'];
    $reminder_time = $_POST['reminder_time'];
    $admin_note = trim($_POST['admin_note'] ?? '');
    
    if (empty($title) || empty($reminder_date) || empty($reminder_time)) {
        $message = "<div class='message error'>❌ Title, date, and time are required.</div>";
    } else {
        // Update the reminder
        $stmt = $conn->prepare("UPDATE reminders SET title = ?, description = ?, reminder_date = ?, reminder_time = ? WHERE reminder_id = ?");
        $stmt->bind_param("ssssi", $title, $description, $reminder_date, $reminder_time, $reminder_id);
        
        if ($stmt->execute()) {
            // Create notification for the user
            $notification_title = "Reminder Updated by Admin";
            $notification_message = "Your reminder '" . htmlspecialchars($title) . "' has been updated by our support team.";
            if (!empty($admin_note)) {
                $notification_message .= " Note: " . $admin_note;
            }
            
            $stmt = $conn->prepare("INSERT INTO notifications (user_email, title, message, type, related_id) VALUES (?, ?, ?, 'support_response', ?)");
            $stmt->bind_param("sssi", $reminder['user_email'], $notification_title, $notification_message, $support_request_id);
            $stmt->execute();
            
            $message = "<div class='message success'>✅ Reminder updated successfully! User has been notified.</div>";
            
            // Redirect back to support requests after a short delay
            header("refresh:2;url=admin_support.php");
        } else {
            $message = "<div class='message error'>❌ Error updating reminder.</div>";
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
    <title>Edit Reminder | Admin | SecondBrain</title>
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
            max-width: 800px;
            margin: 0 auto 40px auto;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #1f74e7;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #1455b8;
        }

        .edit-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }

        .user-info {
            background: rgba(31, 116, 231, 0.1);
            border: 1px solid rgba(31, 116, 231, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .user-info h3 {
            color: #1f74e7;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .user-info p {
            margin: 0.5rem 0;
            color: var(--text-light);
        }

        .form-group {
            margin-bottom: 1.5rem;
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

        .btn {
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(31, 116, 231, 0.4);
        }

        .btn-secondary {
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
            border: 1px solid rgba(31, 116, 231, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(31, 116, 231, 0.2);
        }

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

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .edit-card {
                padding: 1.5rem;
            }

            .button-group {
                flex-direction: column;
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
        <h1>Edit User Reminder</h1>
        <p style="color: #AAB4C3; margin: 0;">Modify user reminder from support request</p>
    </div>

    <div class="container">
        <a href="admin_support.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Support Requests
        </a>
        
        <?php if (!empty($message)) echo $message; ?>

        <div class="edit-card">
            <div class="user-info">
                <h3><i class="fas fa-user"></i> User Information</h3>
                <p><strong>Name:</strong> <?= htmlspecialchars($reminder['user_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($reminder['user_email']) ?></p>
                <p><strong>Reminder ID:</strong> #<?= $reminder['reminder_id'] ?></p>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="title">Reminder Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($reminder['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($reminder['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reminder_date">Reminder Date</label>
                    <input type="date" id="reminder_date" name="reminder_date" class="form-control" value="<?= $reminder['reminder_date'] ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reminder_time">Reminder Time</label>
                    <input type="time" id="reminder_time" name="reminder_time" class="form-control" value="<?= $reminder['reminder_time'] ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="admin_note">Admin Note (Optional)</label>
                    <textarea id="admin_note" name="admin_note" class="form-control" rows="3" placeholder="Add a note to explain the changes made..."></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i>
                        Update Reminder
                    </button>
                    <a href="admin_support.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 