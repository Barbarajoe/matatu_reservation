<?php
require_once 'config.php';

// Verify administrator privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: login.html");
    exit();
}

// Get system statistics
try {
    // Total Users
    $stmt = $conn->query("SELECT COUNT(*) AS total_users FROM users");
    $totalUsers = $stmt->fetchColumn();

    // Active Bookings
    $stmt = $conn->query("SELECT COUNT(*) AS active_bookings FROM bookings WHERE status = 'confirmed'");
    $activeBookings = $stmt->fetchColumn();

    // Available Routes
    $stmt = $conn->query("SELECT COUNT(*) AS total_routes FROM routes WHERE is_active = TRUE");
    $totalRoutes = $stmt->fetchColumn();

    // Recent Audit Logs
    $stmt = $conn->prepare("
        SELECT a.action, a.description, u.username, a.created_at 
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $auditLogs = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $error = "Error loading dashboard data";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1 class="mt-2">Administrator Dashboard</h1>
        
        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <div class="stats-card">
                <h3>Total Users</h3>
                <p><?= number_format($totalUsers) ?></p>
            </div>
            
            <div class="stats-card">
                <h3>Active Bookings</h3>
                <p><?= number_format($activeBookings) ?></p>
            </div>
            
            <div class="stats-card">
                <h3>Available Routes</h3>
                <p><?= number_format($totalRoutes) ?></p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions mt-2">
            <a href="user_management.html" class="btn btn-primary">Manage Users</a>
            <a href="system_report.html" class="btn btn-primary">View System Report</a>
            <a href="audit_logs.html" class="btn btn-primary">View Audit Logs</a>
        </div>

        <!-- Recent Activity Table -->
        <div class="card mt-2">
            <h3>Recent System Activity</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Description</th>
                        <th>User</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($auditLogs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['description']) ?></td>
                        <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                        <td><?= date('M j, Y H:i', strtotime($log['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>