<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "second_brain_v2";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
