<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - User Reports | Second Brain</title>
<link rel="icon" type="image/png" href="logo.png">
<style>
body {
    background: #181F2A;
    color: #F5F7FA;
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
}
h1 {
    text-align: center;
    padding: 20px;
    color: #1f74e7;
}
.container {
    width: 90%;
    margin: 40px auto;
    background: #232B3E;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 14px 10px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    text-align: left;
}
th {
    color: #1f74e7;
    background: rgba(31,116,231,0.1);
}
td {
    color: #F5F7FA;
}
.btn-download {
    background: linear-gradient(135deg, #1f74e7 0%, #1455b8 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-download:hover {
    background: #0e4ea0;
    box-shadow: 0 0 10px rgba(31,116,231,0.5);
    transform: translateY(-2px);
}
</style>
</head>
<body>
    <h1>📄 User Reports</h1>
    <div class="container">
        <table>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>
            <?php
            $count = 1;
            $users = $conn->query("SELECT name, email, phone FROM users ORDER BY name ASC");
            while ($row = $users->fetch_assoc()) {
                echo "<tr>
                    <td>{$count}</td>
                    <td>" . htmlspecialchars($row['name']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['phone']) . "</td>
                    <td>
                        <a href='admin_generate_report.php?email=" . urlencode($row['email']) . "' 
                           target='_blank' 
                           class='btn-download'>
                           <i class='fas fa-file-pdf'></i> Download Report
                        </a>
                    </td>
                </tr>";
                $count++;
            }
            ?>
        </table>
    </div>
</body>
</html>
