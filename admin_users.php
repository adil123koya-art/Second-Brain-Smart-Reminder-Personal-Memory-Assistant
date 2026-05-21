<?php
session_start();
include('db.php');
if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}
$users_result = $conn->query("SELECT user_id, name, email, phone, is_admin FROM users");
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="icon" type="image/png" href="logo.png">
  <title>User Management | Admin | Second Brain</title>
  <style>
    body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #181F2A 0%, #232B3E 100%); color: #F5F7FA; min-height: 100vh; margin: 0; }
    .admin-header { background: #232B3E; padding: 32px 0 18px 0; text-align: center; border-radius: 0 0 18px 18px; box-shadow: 0 4px 16px rgba(24,31,42,0.10); margin-bottom: 36px; }
    .admin-header h1 { color: #1f74e7; font-size: 2.2rem; margin-bottom: 8px; letter-spacing: 1px; }
    .container { max-width: 1100px; margin: 0 auto 40px auto; background: #232B3E; border-radius: 18px; box-shadow: 0 4px 16px rgba(24,31,42,0.12); padding: 36px 30px 30px 30px; }
    h3.section-title { color: #1f74e7; margin-bottom: 18px; font-size: 1.3rem; border-left: 4px solid #20C997; padding-left: 12px; margin-top: 36px; }
    table.admin-table { width: 100%; background: #232B3E; color: #F5F7FA; border-radius: 8px; border-collapse: separate; border-spacing: 0; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(24,31,42,0.08); }
    table.admin-table th, table.admin-table td { padding: 12px 8px; text-align: center; }
    table.admin-table th { background: #1f74e7; color: #fff; font-weight: 600; }
    table.admin-table tr { transition: background 0.15s; }
    table.admin-table tr:hover { background: #20232A; }
    table.admin-table tr:last-child td { border-bottom: none; }
    table.admin-table td { border-bottom: 1px solid #283046; }
    .admin-actions a { margin: 0 2px; padding: 4px 12px; font-size: 13px; border-radius: 5px; }
    .admin-actions .btn { background: #1f74e7; color: #fff; }
    .admin-actions .btn:hover { background: #1455b8; }
    .admin-actions .delete-btn { background: #e74545; color: #fff; }
    .admin-actions .delete-btn:hover { background: #b83232; }
    .back-link { display: inline-block; margin-bottom: 18px; color: #AAB4C3; text-decoration: none; font-size: 1rem; transition: color 0.3s; font-weight: 600; }
    .back-link:hover { color: #1f74e7; }
  </style>
</head>
<body>
  <div class="admin-header">
    <h1>User Management</h1>
  </div>
  <div class="container">
    <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    <table class="admin-table">
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Admin</th>
        <th>Actions</th>
      </tr>
      <?php while($u = $users_result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['phone']) ?></td>
        <td><?= $u['is_admin'] ? 'Yes' : 'No' ?></td>
        <td class="admin-actions">
          
          <a href="delete_user.php?id=<?= $u['user_id'] ?>" class="delete-btn" onclick="return confirm('Delete this user?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html> 