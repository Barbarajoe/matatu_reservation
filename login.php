<?php
require 'config.php'; // Use your PDO connection
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'passenger') {
        header('Location: passenger_home.html');
    } elseif ($_SESSION['role'] === 'operator') {
        header('Location: operator_home.html');
    } elseif ($_SESSION['role'] === 'admin') {
        header('Location: administrator_home.html');
    }
    exit;
}

// Check if $pdo is properly initialized
if (!isset($connect) || is_null($connect)) {
    die("Fatal Error: Database connection is not initialized. Please check db.php.");
}

// Audit log function
function auditLog($action_type, $action_details, $user_id = null) {
    global $connect;
    if (!isset($connect) || is_null($connect)) {
        error_log("Audit Log Error: PDO is not initialized.");
        return;
    }

    $ip_address = $_SERVER['REMOTE_ADDR'];

    try {
        $stmt = $connect->prepare("INSERT INTO audit_logs (user_id, action_type, action_details, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $action_type, $action_details, $ip_address]);
    } catch (PDOException $e) {
        error_log("Audit Log DB Error: " . $e->getMessage());
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ''); // Can be username or email
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Please fill in all fields';
        auditLog('LOGIN_FAILED', "Empty fields for identifier: $identifier", null);
    } else {
        try {
            // Check if the identifier is a username or email
            $stmt = $connect->prepare("SELECT user_id, username, email, password_hash, role, is_verified FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch(); // PDO fetch

            if ($user) {
                if (!$user['is_verified']) {
                    $error = 'Account not verified. Please verify your email.';
                    auditLog('LOGIN_FAILED', "Unverified account for user: {$user['username']}", $user['user_id']);
                } elseif (password_verify($password, $user['password_hash'])) {
                    // Successful login
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    auditLog('LOGIN_SUCCESS', "User {$user['username']} logged in successfully", $user['user_id']);

                    // Redirect based on role
                    if ($user['role'] === 'passenger') {
                        header('Location: passenger_home.html');
                    } elseif ($_SESSION['role'] === 'operator') {
                        header('Location: operator_home.html');
                    } elseif ($_SESSION['role'] === 'admin') {
                        header('Location: administrator_home.html');
                    }
                    exit;
                } else {
                    $error = 'Invalid password';
                    auditLog('LOGIN_FAILED', "Invalid password for user: {$user['username']}", $user['user_id']);
                }
            } else {
                $error = 'Invalid username or email';
                auditLog('LOGIN_FAILED', "Invalid identifier: $identifier", null);
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
            auditLog('LOGIN_ERROR', "Database error: " . $e->getMessage(), null);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Matatu System</title>
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

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
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

        .error {
            color: #721c24;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        .text-center {
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
        <h2>Login to Matatu System</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="identifier">Username or Email</label>
                <input type="text" id="identifier" name="identifier" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <p class="text-center" style="margin-top: 15px;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
            <p class="text-center">
                <a href="forgot_password.php">Forgot Password?</a>
            </p>
        </form>
    </div>
</body>
</html>