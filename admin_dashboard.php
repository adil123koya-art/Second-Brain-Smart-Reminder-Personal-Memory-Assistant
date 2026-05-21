<?php
session_start();
include('db.php');

if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}

$user = ["name" => "SECOND BRAIN!"];

?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="logo.png">
    <title>Admin Dashboard | Second Brain | Your Professional Reminder Solution</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #181F2A;
            color: #F5F7FA;
            min-height: 100vh;
            margin: 0;
        }
        .admin-header {
            background: #232B3E;
            padding: 32px 0 18px 0;
            text-align: center;
            border-radius: 0 0 18px 18px;
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
        .admin-header .admin-welcome {
            font-size: 1.1rem;
            color: #AAB4C3;
        }
        .admin-main-cards {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 60px 0 40px 0;
            flex-wrap: wrap;
        }
        .main-card {
            background: linear-gradient(135deg, #232B3E 60%, #181F2A 100%);
            border-radius: 22px;
            box-shadow: 0 4px 16px rgba(24,31,42,0.12);
            padding: 44px 38px 36px 38px;
            color: #fff;
            text-align: center;
            min-width: 260px;
            max-width: 320px;
            flex: 1 1 260px;
            text-decoration: none;
            transition: transform 0.18s, box-shadow 0.18s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .main-card:hover {
            transform: translateY(-8px) scale(1.04);
            box-shadow: 0 8px 32px rgba(31,116,231,0.18);
        }
        .main-card-icon {
            font-size: 2.8rem;
            margin-bottom: 18px;
        }
        .main-card-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1f74e7;
        }
        .main-card-desc {
            color: #AAB4C3;
            font-size: 1.05rem;
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
            z-index: 2;
        }
        .admin-logout-btn:hover {
            background: #1455b8;
            box-shadow: 0 4px 16px rgba(31,116,231,0.18);
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <a href="logout.php" class="admin-logout-btn">Logout</a>
        <h1>Admin Dashboard</h1>
        <div class="admin-welcome">
            Welcome, <?= htmlspecialchars($user['name']) ?>!<br>
            You have admin access to control the application.
        </div>
    </div>
    <div class="admin-main-cards">
      <a href="admin_users.php" class="main-card">
        <div class="main-card-icon">👥</div>
        <div class="main-card-title">User Management</div>
        <div class="main-card-desc">View, edit, and manage all users</div>
      </a>
      <a href="admin_support.php" class="main-card">
        <div class="main-card-icon">🛠️</div>
        <div class="main-card-title">Support Requests</div>
        <div class="main-card-desc">Help users with reminder issues (with consent)</div>
      </a>
      <a href="admin_complaints.php" class="main-card">
        <div class="main-card-icon">✉️</div>
        <div class="main-card-title">User Complaints & Messages</div>
        <div class="main-card-desc">View and respond to user complaints</div>
      </a>
<div style="text-align: center; margin: 40px;">
    <a href="admin_generate_report.php" 
       style="background: #1f74e7; color: white; padding: 12px 28px; 
              border-radius: 8px; text-decoration: none; font-weight: 600; 
              box-shadow: 0 4px 15px rgba(31,116,231,0.3); transition: 0.3s;">
        📄 Download Admin Report (PDF)
    </a>
</div>


    </div>
</body>
</html> 