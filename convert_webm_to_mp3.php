<?php
// Function to convert webm to mp3 using ffmpeg
function convertWebmToMp3($webmPath, $mp3Path) {
    // Check if ffmpeg is available
    $ffmpegPath = shell_exec('which ffmpeg');
    if (empty($ffmpegPath)) {
        return false; // ffmpeg not available
    }
    
    $ffmpegPath = trim($ffmpegPath);
    $command = "$ffmpegPath -i '$webmPath' -vn -acodec libmp3lame -q:a 2 '$mp3Path' 2>&1";
    
    $output = shell_exec($command);
    
    return file_exists($mp3Path);
}

// Test conversion
$webmFile = 'uploads/voice_1754460990.webm';
$mp3File = 'uploads/voice_1754460990.mp3';

echo "<h2>Convert WebM to MP3</h2>";

if (file_exists($webmFile)) {
    echo "✅ WebM file exists: $webmFile<br>";
    echo "File size: " . filesize($webmFile) . " bytes<br>";
    
    if (convertWebmToMp3($webmFile, $mp3File)) {
        echo "✅ Conversion successful!<br>";
        echo "MP3 file: $mp3File<br>";
        echo "MP3 size: " . filesize($mp3File) . " bytes<br>";
    } else {
        echo "❌ Conversion failed. FFmpeg might not be available.<br>";
    }
} else {
    echo "❌ WebM file not found<br>";
}
?> 