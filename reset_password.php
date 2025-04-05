<?php
require 'config.php'; // Use your PDO connection
session_start();

// Check if $pdo is properly initialized
if (!isset($connect) || is_null($connect)) {
    die("Fatal Error: Database connection is not initialized. Please check db.php.");
}

// Audit log function
function auditLog($action_type, $action_details, $user_id = null) {
    global $connect; // Ensure $pdo is available in this scope
    if (!isset($connect) || is_null($connect)) {
        error_log("Audit Log Error: PDO is not initialized.");
        return; // Skip logging if $pdo is not available
    }

    $ip_address = $_SERVER['REMOTE_ADDR'];

    try {
        $stmt = $connect->prepare("INSERT INTO audit_logs (user_id, action_type, action_details, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $action_type, $action_details, $ip_address]);
    } catch (PDOException $e) {
        error_log("Audit Log DB Error: " . $e->getMessage());
    }
}

$token = $_GET['token'] ?? '';
$message = '';
$error = '';

if (empty($token)) {
    $error = 'Invalid or missing reset token';
    auditLog('PASSWORD_RESET_FAILED', "Missing reset token", null);
} else {
    try {
        // Check if the token is valid and not expired
        $stmt = $connect->prepare("SELECT user_id, email FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Invalid or expired reset token';
            auditLog('PASSWORD_RESET_FAILED', "Invalid or expired token: $token", null);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($password) || empty($confirm_password)) {
                $error = 'Please fill in all fields';
                auditLog('PASSWORD_RESET_FAILED', "Empty fields for user ID: {$user['user_id']}", $user['user_id']);
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match';
                auditLog('PASSWORD_RESET_FAILED', "Passwords do not match for user ID: {$user['user_id']}", $user['user_id']);
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long';
                auditLog('PASSWORD_RESET_FAILED', "Password too short for user ID: {$user['user_id']}", $user['user_id']);
            } else {
                // Update the password
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE user_id = ?");
                $stmt->execute([$password_hash, $user['user_id']]);

                $message = 'Your password has been reset successfully. You can now <a href="login.php">login</a>.';
                auditLog('PASSWORD_RESET_SUCCESS', "Password reset for email: {$user['email']}", $user['user_id']);
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
        auditLog('PASSWORD_RESET_ERROR', "Database error: " . $e->getMessage(), null);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Matatu System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #555;
            margin-bottom: 8px;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        input[type="password"]:focus {
            border-color: #ff6600;
            background-color: #fff;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            color: white;
            background-color: #ff6600;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #e65c00;
        }

        .message {
            color: #155724;
            background-color: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error {
            color: #721c24;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        a {
            color: #ff6600;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php elseif ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>