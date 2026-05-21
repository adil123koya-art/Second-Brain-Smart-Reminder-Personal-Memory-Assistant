<?php
session_start();
include('db.php');
if (!isset($_SESSION["email"]) || !isset($_SESSION["is_admin"])) {
    header("Location: admin_login.php");
    exit;
}
$reminders_result = $conn->query("SELECT reminders.reminder_id, reminders.title, reminders.description, reminders.reminder_date, reminders.reminder_time, users.email FROM reminders JOIN users ON reminders.user_email = users.email");
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="icon" type="image/png" href="logo.png">
  <title>Reminders Management | Admin | Second Brain</title>
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
    <h1>Reminders Management</h1>
  </div>
  <div class="container">
    <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    
    <?php if (isset($_GET['success'])): ?>
      <div class="message success" style="background: rgba(32, 201, 151, 0.2); color: #20C997; border: 1px solid rgba(32, 201, 151, 0.3); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
        ✅ Reminder deleted successfully!
      </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
      <div class="message error" style="background: rgba(231, 69, 69, 0.2); color: #ff6b6b; border: 1px solid rgba(231, 69, 69, 0.3); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
        ❌ Error deleting reminder. Please try again.
      </div>
    <?php endif; ?>
    <table class="admin-table">
      <tr>
        <th>Title</th>
        <th>Description</th>
        <th>User Email</th>
        <th>Date</th>
        <th>Time</th>
        <th>Actions</th>
      </tr>
      <?php while($r = $reminders_result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['title']) ?></td>
        <td><?= htmlspecialchars($r['description']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= htmlspecialchars($r['reminder_date']) ?></td>
        <td><?= htmlspecialchars($r['reminder_time']) ?></td>
        <td class="admin-actions">
          <a href="admin_edit_reminder.php?id=<?= $r['reminder_id'] ?>" class="btn">Edit</a>
          <a href="admin_delete_reminder.php?id=<?= $r['reminder_id'] ?>" class="delete-btn" onclick="return confirm('Delete this reminder?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html> 