<?php
require_once 'config.php';

// Verify administrator privileges
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: administrator_home.php");
    exit();
}

$userId = (int)$_GET['id'];
$error = '';
$success = '';

// Fetch user data
try {
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "User not found";
        header("Location: administrator_home.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching user data: " . $e->getMessage();
    header("Location: administrator_home.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $changePassword = isset($_POST['change_password']);
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($email)) {
        $error = 'Username and email are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($changePassword && strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        try {
            // Check if username or email already exists for another user
            $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $userId]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Username or email already exists';
            } else {
                if ($changePassword) {
                    // Update with password change
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, password_hash = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $passwordHash, $userId]);
                } else {
                    // Update without password change
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $userId]);
                }
                
                // Log the action
                $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
                $logStmt->execute([
                    $_SESSION['user_id'],
                    'user_update',
                    "Updated user account: $username",
                    $_SERVER['REMOTE_ADDR']
                ]);
                
                $_SESSION['success'] = "User updated successfully";
                header("Location: administrator_home.php");
                exit();
            }
        } catch(PDOException $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Edit User</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                                    <option value="operator" <?= $user['role'] === 'operator' ? 'selected' : '' ?>>Operator</option>
                                    <option value="passenger" <?= $user['role'] === 'passenger' ? 'selected' : '' ?>>Passenger</option>
                                </select>
                            </div>
                            
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="change_password" name="change_password">
                                <label class="form-check-label" for="change_password">Change Password</label>
                            </div>
                            
                            <div class="form-group password-field" style="display: none;">
                                <label for="password">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" minlength="8">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="administrator_home.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#change_password').change(function() {
                if(this.checked) {
                    $('.password-field').show();
                    $('#password').attr('required', true);
                } else {
                    $('.password-field').hide();
                    $('#password').attr('required', false);
                }
            });
        });
    </script>
</body>
</html>