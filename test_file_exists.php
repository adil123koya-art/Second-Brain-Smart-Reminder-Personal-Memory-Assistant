<?php
$voiceFile = 'uploads/voice_1754461667.mp3';

echo "<h2>Test File Existence</h2>";
echo "File: $voiceFile<br>";

if (file_exists($voiceFile)) {
    echo "✅ File exists<br>";
    echo "File size: " . filesize($voiceFile) . " bytes<br>";
    echo "File permissions: " . substr(sprintf('%o', fileperms($voiceFile)), -4) . "<br>";
    
    if (is_readable($voiceFile)) {
        echo "✅ File is readable<br>";
    } else {
        echo "❌ File is NOT readable<br>";
    }
    
    if (is_file($voiceFile)) {
        echo "✅ It's a file<br>";
    } else {
        echo "❌ It's NOT a file<br>";
    }
    
    // Test absolute path
    $absolutePath = realpath($voiceFile);
    echo "Absolute path: $absolutePath<br>";
    
} else {
    echo "❌ File does not exist<br>";
    
    // Check if directory exists
    $dir = dirname($voiceFile);
    if (is_dir($dir)) {
        echo "✅ Directory exists: $dir<br>";
        
        // List files in directory
        $files = scandir($dir);
        echo "Files in directory:<br>";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "- $file<br>";
            }
        }
    } else {
        echo "❌ Directory does not exist: $dir<br>";
    }
}
?> 