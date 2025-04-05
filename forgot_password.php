<?php
require 'config.php'; // Use your PDO connection
session_start();

// Audit log function
function auditLog($action_type, $action_details, $user_id = null) {
    global $connect; // Use the correct database connection variable
    $ip_address = $_SERVER['REMOTE_ADDR'];

    try {
        $stmt = $connect->prepare("INSERT INTO audit_logs (user_id, action_type, action_details, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $action_type, $action_details, $ip_address]);
    } catch (PDOException $e) {
        error_log("Audit Log DB Error: " . $e->getMessage());
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Please enter your email address';
        auditLog('PASSWORD_RESET_REQUEST_FAILED', "Empty email field", null);
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
        auditLog('PASSWORD_RESET_REQUEST_FAILED', "Invalid email format: $email", null);
    } else {
        try {
            // Check if the email exists
            $stmt = $connect->prepare("SELECT user_id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Generate a reset token
                $reset_token = bin2hex(random_bytes(32));
                $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Update the user with the reset token and expiration
                $stmt = $connect->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
                $stmt->execute([$reset_token, $reset_expires, $email]);

                // Create the reset link
                $reset_link = "http://localhost/reset_password.php?token=$reset_token";

                // Send the reset email
                $to = $email;
                $subject = "Password Reset Request - Matatu System";
                $body = "Hello {$user['username']},\n\n";
                $body .= "You have requested to reset your password. Click the link below to reset your password:\n";
                $body .= "$reset_link\n\n";
                $body .= "This link will expire in 1 hour. If you did not request a password reset, please ignore this email.\n\n";
                $body .= "Best regards,\nMatatu System Team";
                $headers = "From: no-reply@matatusystem.com\r\n";
                $headers .= "Reply-To: no-reply@matatusystem.com\r\n";

                if (mail($to, $subject, $body, $headers)) {
                    $message = 'A password reset link has been sent to your email address.';
                    auditLog('PASSWORD_RESET_REQUEST_SUCCESS', "Reset link sent to email: $email", $user['user_id']);
                } else {
                    $error = 'Failed to send the reset email. Please try again later.';
                    auditLog('PASSWORD_RESET_REQUEST_FAILED', "Failed to send email to: $email", $user['user_id']);
                }
            } else {
                $error = 'No account found with that email address';
                auditLog('PASSWORD_RESET_REQUEST_FAILED', "No account found for email: $email", null);
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
            auditLog('PASSWORD_RESET_REQUEST_ERROR', "Database error: " . $e->getMessage(), null);
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
            auditLog('PASSWORD_RESET_REQUEST_ERROR', "General error: " . $e->getMessage(), null);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Matatu System</title>
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

        input[type="email"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus {
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
        <h2>Forgot Password</h2>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Send Reset Link</button>
            <p class="text-center" style="margin-top: 15px;">
                Remember your password? <a href="login.php">Login here</a>
            </p>
        </form>
    </div>
</body>
</html>