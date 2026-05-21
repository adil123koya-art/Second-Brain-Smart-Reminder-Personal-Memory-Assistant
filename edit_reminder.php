<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION["email"];
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "Invalid request.";
    exit;
}

// Fetch user info for sidebar
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultUser = $stmt->get_result();
$user = $resultUser->fetch_assoc();

// Fetch reminder
$stmt = $conn->prepare("SELECT * FROM reminders WHERE reminder_id=? AND user_email=?");
$stmt->bind_param("is", $id, $email);
$stmt->execute();
$result = $stmt->get_result();
$reminder = $result->fetch_assoc();

if (!$reminder) {
    echo "Reminder not found.";
    exit;
}

$message = "";
$voice_path = $reminder['voice_file'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $date = $_POST["date"];
    $time = $_POST["time"];

    // Replace voice if new one submitted
    if (isset($_POST['voiceData']) && !empty($_POST['voiceData'])) {
        if (!empty($voice_path) && file_exists($voice_path)) {
            unlink($voice_path); // delete old voice
        }
        $voiceData = $_POST["voiceData"];
        $voiceData = str_replace('data:audio/webm;base64,', '', $voiceData);
        $voiceData = base64_decode($voiceData);
        $voice_path = "uploads/voice_" . time() . ".webm";
        file_put_contents($voice_path, $voiceData);
    }

    $update = $conn->prepare("UPDATE reminders SET title=?, description=?, reminder_date=?, reminder_time=?, voice_file=?, updated_at=NOW() WHERE reminder_id=? AND user_email=?");
    $update->bind_param("sssssis", $title, $description, $date, $time, $voice_path, $id, $email);

    if ($update->execute()) {
        $message = "<div class='message success'>✅ Reminder updated successfully!</div>";
        $reminder['title'] = $title;
        $reminder['description'] = $description;
        $reminder['reminder_date'] = $date;
        $reminder['reminder_time'] = $time;
        $reminder['voice_file'] = $voice_path;
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
    <title>Edit Reminder | SecondBrain</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles - Same as dashboard */
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

          .sidebar-user {
            display: flex;
            align-items: center;
            justify-content:center;
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


        .page-title i {
            color: #1f74e7;
            font-size: 2rem;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        /* Form Card Styles */
        .form-card {
            background: rgba(35, 43, 62, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(31, 116, 231, 0.1);
            padding: 2.5rem;
            max-width: 680px;
            margin: 0 auto;
        }

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

        textarea.form-control {
            height: auto;
            min-height: 120px;
            resize: vertical;
            padding: 1rem;
        }

        /* Date and Time inputs specific styling */
        input[type="date"].form-control,
        input[type="time"].form-control {
            padding: 0 1rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        /* Make calendar and clock icons white */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.8;
            cursor: pointer;
        }

        /* For Firefox */
        input[type="date"]::-moz-calendar-picker-indicator,
        input[type="time"]::-moz-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.8;
            cursor: pointer;
        }

        .date-time-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Voice Recording Styles */
        .voice-section {
            text-align: center;
            margin: 2rem 0;
            padding: 2rem;
            background: rgba(31, 116, 231, 0.05);
            border-radius: 16px;
            border: 1px dashed rgba(31, 116, 231, 0.2);
        }

        .mic-btn {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(31, 116, 231, 0.1);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            transition: all 0.3s ease;
        }

        .mic-btn i {
            font-size: 2rem;
            color: #1f74e7;
            transition: all 0.3s ease;
        }

        .mic-btn.recording {
            background: #1f74e7;
            animation: pulse 1.5s infinite;
        }

        .mic-btn.recording i {
            color: white;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(31, 116, 231, 0.4);
            }
            70% {
                box-shadow: 0 0 0 20px rgba(31, 116, 231, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(31, 116, 231, 0);
            }
        }

        .mic-label {
            color: #AAB4C3;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .recording-status {
            color: #1f74e7;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .recording-status .dot {
            width: 8px;
            height: 8px;
            background: #1f74e7;
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        audio {
            width: 100%;
            margin-top: 1rem;
            border-radius: 12px;
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
            .date-time-group {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 1.5rem;
            }
        }
    </style>
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
            <nav class="sidebar-menu" aria-label="Main Navigation">
                <a href="dashboard.php" class="menu-item"><i class="fas fa-home"></i></a>
                <a href="add_reminder.php" class="menu-item"><i class="fas fa-plus"></i></a>
                <a href="view_reminder.php" class="menu-item active"><i class="fas fa-list"></i></a>
                <a href="my_complaints.php" class="menu-item"><i class="fas fa-envelope"></i></a>
                <a href="profile.php" class="menu-item"><i class="fas fa-user"></i></a>
                <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i></a>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-edit" aria-hidden="true" style="color:#1f74e7;"></i>
                    Edit Reminder
                </h1>
                <p class="page-subtitle">Update your reminder or voice note below.</p>
            </div>
            <?php if (!empty($message)) {
                $icon = strpos($message, "success") !== false ? "fa-circle-check" : "fa-circle-exclamation";
                echo str_replace('class="message', 'class="message" role="alert"><i class="fas ' . $icon . '" aria-hidden="true"></i>', $message);
            } ?>
            <div class="form-card" role="form">
                <form method="POST" enctype="multipart/form-data" autocomplete="off">
                    <div class="form-group">
                        <label class="form-label" for="title">Reminder Title</label>
                        <input type="text" id="title" name="title" class="form-control" required placeholder="Enter reminder title" value="<?= htmlspecialchars($reminder['title']) ?>" aria-label="Reminder Title">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" required placeholder="Enter reminder description" aria-label="Description"><?= htmlspecialchars($reminder['description']) ?></textarea>
                    </div>
                    <div class="date-time-group">
                        <div class="form-group" style="position:relative;">
                            <label class="form-label" for="date">Date</label>
                            <input type="date" id="date" name="date" class="form-control" required value="<?= $reminder['reminder_date'] ?>" aria-label="Date" style="padding-right:2.5rem;">
                            <span style="position:absolute;right:1rem;top:3.1rem;color:#AAB4C3;"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <div class="form-group" style="position:relative;">
                            <label class="form-label" for="time">Time</label>
                            <input type="time" id="time" name="time" class="form-control" required value="<?= $reminder['reminder_time'] ?>" aria-label="Time" style="padding-right:2.5rem;">
                            <span style="position:absolute;right:1rem;top:3.1rem;color:#AAB4C3;"><i class="fas fa-clock"></i></span>
                        </div>
                    </div>
                    <div class="voice-section">
                        <button type="button" id="mic-btn" class="mic-btn" onclick="toggleRecording()" aria-label="Record Voice Note">
                            <i class="fas fa-microphone"></i>
                        </button>
                        <div class="mic-label">Add/Replace Voice Note (Optional)</div>
                        <div id="recording-status" class="recording-status" style="display: none;">
                            <span class="dot"></span>
                            Recording...
                        </div>
                        <?php if (!empty($reminder['voice_file']) && file_exists($reminder['voice_file'])): ?>
                            <audio id="existing-audio" controls src="<?= $reminder['voice_file'] ?>" style="width:100%; margin-top:1rem; border-radius:12px;" aria-label="Existing Voice Note"></audio>
                        <?php endif; ?>
                        <audio id="player" controls style="display:none; border-radius:12px;" aria-label="Recorded Voice Note"></audio>
                        <input type="hidden" name="voiceData" id="voiceData">
                    </div>
                    <button type="submit" class="submit-btn" aria-label="Update Reminder">
                        <i class="fas fa-save"></i>
                        Update Reminder
                    </button>
                </form>
            </div>
        </main>
    </div>
<script>
let mediaRecorder;
let audioChunks = [];
let isRecording = false;
let micStream = null;

function toggleRecording() {
    const micBtn = document.getElementById('mic-btn');
    const recordingStatus = document.getElementById('recording-status');
    if (!isRecording) {
        navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
            micStream = stream;
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            mediaRecorder.start();
            isRecording = true;
            micBtn.classList.add('recording');
            recordingStatus.style.display = 'flex';
            mediaRecorder.ondataavailable = e => {
                audioChunks.push(e.data);
            };
            mediaRecorder.onstop = () => {
                const blob = new Blob(audioChunks, { type: 'audio/webm' });
                const url = URL.createObjectURL(blob);
                document.getElementById('player').src = url;
                document.getElementById('player').style.display = 'block';
                const reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
                    document.getElementById("voiceData").value = reader.result;
                };
                if (micStream) {
                    micStream.getTracks().forEach(track => track.stop());
                    micStream = null;
                }
            };
        });
    } else {
        if (mediaRecorder) {
            mediaRecorder.stop();
            isRecording = false;
            micBtn.classList.remove('recording');
            recordingStatus.style.display = 'none';
        }
    }
}
</script>
</body>
</html>