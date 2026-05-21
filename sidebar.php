<!-- sidebar.php -->
<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background-color: #0f172a;
        color: #fff;
        display: flex;
        flex-direction: column;
        padding-top: 40px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
        z-index: 100;
    }

    .sidebar h2 {
        text-align: center;
        font-size: 24px;
        margin-bottom: 30px;
        color: #38bdf8;
    }

    .sidebar a {
        text-decoration: none;
        color: #cbd5e1;
        padding: 15px 20px;
        display: block;
        font-size: 16px;
        transition: background 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #1e293b;
        color: #fff;
    }

    .sidebar a.active {
        background-color: #1e293b;
        color: #38bdf8;
    }

    .sidebar a.logout {
        margin-top: auto;
        background-color: #ef4444;
        color: #fff;
        text-align: center;
        border-top: 1px solid #334155;
    }

    .sidebar a.logout:hover {
        background-color: #dc2626;
    }
</style>

<div class="sidebar">
    <h2>Second Brain</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="add_reminder.php">➕ Add Reminder</a>
    <a href="view_reminder.php" class="active">📋 View Reminders</a>
    <a href="messages.php">✉️ Messages</a>
    <a href="profile.php">👤 Profile</a>
    <a href="logout.php" class="logout">🚪 Logout</a>
</div>
