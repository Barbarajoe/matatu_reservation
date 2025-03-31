<?php
require 'config.php';

function auditLog($action, $message) {
    global $conn; // Use the database connection
    // Log to a file (optional)
    $logFile = 'audit.log';
    $logMessage = "[" . date("Y-m-d H:i:s") . "] [$action] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    try {
        // Insert log into the database
        $stmt = $conn->prepare("INSERT INTO audit_logs (action, message, log_time) VALUES (?, ?, NOW())");
        $stmt->execute([$action, $message]);
    } catch (Exception $e) {
        // Fallback logging if database fails
        error_log("Audit Log DB Error: " . $e->getMessage());
}
}

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Get current user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("User not found");
        }

        // Sanitize inputs
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validate phone number (Kenya format)
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        if (!preg_match('/^2547[0-9]{8}$/', $clean_phone)) {
            throw new Exception("Invalid phone number format. Use 2547XXXXXXXX");
        }

        // Check password change
        $password_update = '';
        if (!empty($new_password)) {
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
            
            // Validate new password strength
            if (strlen($new_password) < 8) {
                throw new Exception("Password must be at least 8 characters");
            }
            $password_hash = password_hash($new_password, PASSWORD_ARGON2ID);
            $password_update = ", password = :password";
        }

        // Check email uniqueness
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already exists");
        }

        // Update query
        $sql = "UPDATE users SET 
                email = :email,
                phone = :phone
                $password_update
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $params = [
            ':email' => $email,
            ':phone' => $clean_phone,
            ':id' => $_SESSION['user_id']
        ];

        if (!empty($password_update)) {
            $params[':password'] = $password_hash;
        }

        $stmt->execute($params);

        // Update session data
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $clean_phone;

        $conn->commit();
        auditLog('profile_update', "Profile updated successfully");
        header("Location: profile.html?success=Profile updated successfully");
        
    } catch (PDOException $e) {
        $conn->rollBack();
        auditLog('profile_update_error', $e->getMessage());
        header("Location: profile.html?error=Database error");
    } catch (Exception $e) {
        $conn->rollBack();
        auditLog('profile_update_error', $e->getMessage());
        header("Location: profile.html?error=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: profile.html");
}
exit();
?>