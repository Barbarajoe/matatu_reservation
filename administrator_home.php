<?php
require_once 'config.php';

// Verify administrator privileges
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $userId = (int)$_GET['delete_user'];
    
    try {
        $conn->beginTransaction();
        
        // Delete dependent records first
        $conn->exec("DELETE FROM bookings WHERE user_id = $userId");
        $conn->exec("DELETE FROM routes WHERE created_by = $userId");
        
        // Then delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        $conn->commit();
        
        // Log the action
        $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $logStmt->execute([
            $_SESSION['user_id'],
            'user_delete',
            "Deleted user ID $userId",
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $_SESSION['success'] = "User deleted successfully";
        header("Location: administrator_home.php");
        exit();
    } catch(PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Failed to delete user: " . $e->getMessage();
        header("Location: administrator_home.php");
        exit();
    }
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

    // Get all users for management
    $usersStmt = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
    $allUsers = $usersStmt->fetchAll();

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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .stats-card {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            font-size: 1.2rem;
            color: #6c757d;
        }
        .stats-card p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }
        .data-table {
            width: 100%;
        }
        .action-btns .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <h1 class="mb-4">Administrator Dashboard</h1>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <h3>Total Users</h3>
                    <p><?= number_format($totalUsers) ?></p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card">
                    <h3>Active Bookings</h3>
                    <p><?= number_format($activeBookings) ?></p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card">
                    <h3>Available Routes</h3>
                    <p><?= number_format($totalRoutes) ?></p>
                </div>
            </div>
        </div>

        <!-- User Management Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">User Management</h3>
                <a href="add_user.php" class="btn btn-primary">Add New User</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($allUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($user['role'])) ?></td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td class="action-btns">
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                    <a href="administrator_home.php?delete_user=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Recent System Activity</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped data-table">
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
                                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $log['action']))) ?></td>
                                <td><?= htmlspecialchars($log['description']) ?></td>
                                <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                <td><?= date('M j, Y H:i', strtotime($log['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>