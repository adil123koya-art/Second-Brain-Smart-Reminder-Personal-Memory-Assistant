<?php
include('db.php');

echo "<h2>Voice File Test Report</h2>";

// Check uploads directory
$uploadsDir = 'uploads/';
echo "<h3>1. Uploads Directory Check</h3>";
if (is_dir($uploadsDir)) {
    echo "✅ Uploads directory exists<br>";
    if (is_readable($uploadsDir)) {
        echo "✅ Uploads directory is readable<br>";
    } else {
        echo "❌ Uploads directory is NOT readable<br>";
    }
    if (is_writable($uploadsDir)) {
        echo "✅ Uploads directory is writable<br>";
    } else {
        echo "❌ Uploads directory is NOT writable<br>";
    }
} else {
    echo "❌ Uploads directory does NOT exist<br>";
}

// Check voice files in database
echo "<h3>2. Voice Files in Database</h3>";
$stmt = $conn->prepare("SELECT reminder_id, user_email, title, voice_file FROM reminders WHERE voice_file IS NOT NULL AND voice_file != '' ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Reminder ID</th><th>User Email</th><th>Title</th><th>Voice File Path</th><th>File Exists</th><th>File Size</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $voicePath = $row['voice_file'];
        $fileExists = file_exists($voicePath);
        $fileSize = $fileExists ? filesize($voicePath) : 'N/A';
        
        echo "<tr>";
        echo "<td>" . $row['reminder_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($voicePath) . "</td>";
        echo "<td>" . ($fileExists ? "✅ Yes" : "❌ No") . "</td>";
        echo "<td>" . $fileSize . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No voice files found in database<br>";
}

// Test file attachment
echo "<h3>3. Test File Attachment</h3>";
$testFile = 'uploads/voice_1751273150_4096.jpeg'; // Using an existing file from your uploads
if (file_exists($testFile)) {
    echo "✅ Test file exists: $testFile<br>";
    echo "File size: " . filesize($testFile) . " bytes<br>";
    echo "File permissions: " . substr(sprintf('%o', fileperms($testFile)), -4) . "<br>";
    
    // Test if file is readable
    if (is_readable($testFile)) {
        echo "✅ File is readable<br>";
    } else {
        echo "❌ File is NOT readable<br>";
    }
} else {
    echo "❌ Test file does not exist: $testFile<br>";
}

// List all files in uploads directory
echo "<h3>4. All Files in Uploads Directory</h3>";
$files = glob($uploadsDir . '*');
if ($files) {
    echo "<ul>";
    foreach ($files as $file) {
        $fileSize = filesize($file);
        $filePerms = substr(sprintf('%o', fileperms($file)), -4);
        echo "<li>" . basename($file) . " (Size: $fileSize bytes, Perms: $filePerms)</li>";
    }
    echo "</ul>";
} else {
    echo "No files found in uploads directory<br>";
}

echo "<h3>5. PHP Configuration</h3>";
echo "Upload max filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post max size: " . ini_get('post_max_size') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . " seconds<br>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
?> 