<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION["email"];
$message = "";

// Fetch user info
$stmt = $conn->prepare("SELECT name, phone, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $profile_picture = $user['profile_picture'];
    $new_password = $_POST["password"] ?? '';

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
            ];
            $message = "<div class='message error'>❌ Upload error: " . ($upload_errors[$_FILES['profile_picture']['error']] ?? 'Unknown error') . "</div>";
        } else {
            $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
            $fileName = basename($_FILES['profile_picture']['name']);
            $fileSize = $_FILES['profile_picture']['size'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($fileExt, $allowed)) {
                $message = "<div class='message error'>❌ Only JPG, JPEG, PNG, GIF allowed.</div>";
            } elseif ($fileSize > $maxSize) {
                $message = "<div class='message error'>❌ File size exceeds 5MB limit.</div>";
            } elseif (!is_writable('uploads/')) {
                $message = "<div class='message error'>❌ Upload directory is not writable.</div>";
            } else {
                $newFileName = 'uploads/profile_' . time() . '_' . rand(1000,9999) . '.' . $fileExt;
                if (move_uploaded_file($fileTmpPath, $newFileName)) {
                    // Delete old picture if exists
                    if (!empty($profile_picture) && file_exists($profile_picture)) {
                        unlink($profile_picture);
                    }
                    $profile_picture = $newFileName;
                } else {
                    $message = "<div class='message error'>❌ Failed to move uploaded file.</div>";
                }
            }
        }
    }

    // Update user info
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET name=?, phone=?, profile_picture=?, password=? WHERE email=?");
        $update->bind_param("sssss", $name, $phone, $profile_picture, $hashed_password, $email);
    } else {
        $update = $conn->prepare("UPDATE users SET name=?, phone=?, profile_picture=? WHERE email=?");
        $update->bind_param("ssss", $name, $phone, $profile_picture, $email);
    }
    if ($update->execute()) {
        $message = "<div class='message success'>✅ Profile updated successfully!</div>";
        $user['name'] = $name;
        $user['phone'] = $phone;
        $user['profile_picture'] = $profile_picture;
    } else {
        $message = "<div class='message error'>❌ Error: " . $update->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <title>Profile Settings | SecondBrain</title>
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

        /* Profile Form Card */
        .profile-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2.5rem;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
            border-radius: 24px 24px 0 0;
        }

        /* Profile Picture Section */
        .profile-picture-section {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: rgba(31, 116, 231, 0.05);
            border-radius: 16px;
            border: 1px dashed rgba(31, 116, 231, 0.2);
        }

        .current-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(31, 116, 231, 0.3);
            margin-bottom: 1rem;
            box-shadow: 0 8px 25px rgba(31, 116, 231, 0.2);
        }

        .file-upload-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .file-upload-btn {
            background: rgba(31, 116, 231, 0.1);
            color: #1f74e7;
            border: 1px solid rgba(31, 116, 231, 0.2);
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .file-upload-btn:hover {
            background: rgba(31, 116, 231, 0.2);
            border-color: rgba(31, 116, 231, 0.4);
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .upload-info {
            color: #8a9ba8;
            font-size: 0.9rem;
            margin-top: 0.5rem;
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

        .form-control:disabled {
            background: rgba(15, 20, 25, 0.4);
            color: #8a9ba8;
            cursor: not-allowed;
            border-color: rgba(31, 116, 231, 0.1);
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
            .profile-card {
                padding: 1.5rem;
                margin: 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .current-picture {
                width: 100px;
                height: 100px;
            }
        }
        .pdf-link-btn {
    display: inline-block;
    padding: 8px 16px;
    font-size: 0.9rem;
    font-weight: 500;
    background-color: rgba(31, 116, 231, 0.15);
    color: #1f74e7;
    border: 1px solid rgba(31, 116, 231, 0.3);
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.pdf-link-btn:hover {
    background-color: rgba(31, 116, 231, 0.3);
    color: #fff;
    box-shadow: 0 0 8px rgba(31, 116, 231, 0.4);
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
                <a href="support_dashboard.php" class="menu-item">
                    <i class="fas fa-headset"></i>
                    <span>Support</span>
                </a>
                <a href="profile.php" class="menu-item active">
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
                    <i class="fas fa-user-edit"></i>
                    Profile Settings
                </h1>
                <p class="page-subtitle">Update your profile information and settings</p>
            </div>

            <div class="profile-card">
                <?php if (!empty($message)): ?>
                    <div class="message <?= strpos($message, "success") !== false ? 'success' : 'error' ?>">
                        <i class="fas <?= strpos($message, "success") !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                        <?= str_replace(['<div class="message success">', '<div class="message error">', '</div>'], '', $message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" autocomplete="off">
                    <!-- Profile Picture Section -->
                    <div class="profile-picture-section">
                        <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'logo.png' ?>" alt="Profile Picture" class="current-picture">
                        <div class="file-upload-container">
                            <label for="profile_picture" class="file-upload-btn">
                                <i class="fas fa-camera"></i>
                                Change Profile Picture
                            </label>
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="file-input">
                        </div>
                        <div class="upload-info">
                            <i class="fas fa-info-circle"></i>
                            Supported formats: JPG, JPEG, PNG, GIF (Max 5MB)
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your full name" value="<?= htmlspecialchars($user['name']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-control" required placeholder="Enter your phone number" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" disabled>
                        <small style="color: #8a9ba8; font-size: 0.85rem; margin-top: 0.5rem; display: block;">
                            <i class="fas fa-lock"></i>
                            Email address cannot be changed
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password (leave blank to keep current)">
                        <small style="color: #8a9ba8; font-size: 0.85rem; margin-top: 0.5rem; display: block;">
                            <i class="fas fa-info-circle"></i>
                            Leave blank if you don't want to change your password
                        </small>
                    </div>

                 <!-- Save Profile Button -->
<button type="submit" name="save_profile" class="submit-btn">
    <i class="fas fa-save"></i> Save Changes
</button>
</form>

<!-- Download Report Button -->
<div style="text-align: center; margin-top: 0.8rem;">
    <a href="generate_report.php?user_id=<?= $user_id ?>" target="_blank" class="pdf-link-btn">
        <i class="fas fa-file-pdf"></i> Download Report (PDF)
    </a>
</div>

            </div>
        </main>
    </div>
</body>
</html> 