<?php
include('db.php');

// Create a test reminder for current time
$email = 'adil123koya@gmail.com';
$title = 'Test Voice Attachment';
$description = 'Testing voice file attachment in real reminder';
$date = date('Y-m-d');
$time = date('H:i');
$voice_file = 'uploads/voice_1754461667.mp3'; // Use existing voice file

echo "<h2>Creating Test Reminder</h2>";
echo "Email: $email<br>";
echo "Title: $title<br>";
echo "Date: $date<br>";
echo "Time: $time<br>";
echo "Voice file: $voice_file<br>";

// Insert test reminder
$stmt = $conn->prepare("INSERT INTO reminders (user_email, title, description, reminder_date, reminder_time, voice_file, created_at, updated_at, is_sent) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), 0)");
$stmt->bind_param("ssssss", $email, $title, $description, $date, $time, $voice_file);

if ($stmt->execute()) {
    echo "✅ Test reminder created successfully!<br>";
    echo "Reminder ID: " . $conn->insert_id . "<br>";
    echo "The cron job should send this reminder in the next minute.<br>";
} else {
    echo "❌ Error creating test reminder: " . $stmt->error . "<br>";
}
?> 