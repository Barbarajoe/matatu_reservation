<?php
require 'config.php'; // Use your PDO connection from db.php
session_start();

// Function to sanitize input (assuming this is what sanitizeInput does)
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Audit log function adapted to your audit_logs table
function auditLog($action_type, $action_details) {
    global $pdo; // Use the PDO connection
    $user_id = $_SESSION['user_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Log to a file (optional)
    $logFile = 'audit.log';
    $logMessage = "[" . date("Y-m-d H:i:s") . "] [$action_type] $action_details" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action_type, action_details, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $action_type, $action_details, $ip_address]);
    } catch (PDOException $e) {
        error_log("Audit Log DB Error: " . $e->getMessage());
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Get current user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
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

        // Validate phone number (Kenya format: 2547XXXXXXXX)
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        if (!preg_match('/^2547[0-9]{8}$/', $clean_phone)) {
            throw new Exception("Invalid phone number format. Use 2547XXXXXXXX");
        }

        // Check password change
        $password_update = '';
        $params = [
            ':email' => $email,
            // Note: 'phone' column doesn't exist yet; we'll add it below
            ':user_id' => $_SESSION['user_id']
        ];
        if (!empty($new_password)) {
            if (!password_verify($current_password, $user['password_hash'])) {
                throw new Exception("Current password is incorrect");
            }
            if (strlen($new_password) < 8) {
                throw new Exception("Password must be at least 8 characters");
            }
            $password_hash = password_hash($new_password, PASSWORD_ARGON2ID);
            $password_update = ", password_hash = :password_hash";
            $params[':password_hash'] = $password_hash;
        }

        // Check email uniqueness
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already exists");
        }

        // Update query (phone column needs to be added to schema first)
        $sql = "UPDATE users SET 
                email = :email
                $password_update
                WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Update session data
        $_SESSION['email'] = $email;
        // $_SESSION['phone'] = $clean_phone; // Uncomment once phone column is added

        $pdo->commit();
        auditLog('PROFILE_UPDATE', "Profile updated successfully for user {$_SESSION['username']}");
        header("Location: profile.php?success=Profile updated successfully");
    } catch (PDOException $e) {
        $pdo->rollBack();
        auditLog('PROFILE_UPDATE_ERROR', $e->getMessage());
        header("Location: profile.php?error=Database error");
    } catch (Exception $e) {
        $pdo->rollBack();
        auditLog('PROFILE_UPDATE_ERROR', $e->getMessage());
        header("Location: profile.php?error=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: profile.php");
}
exit();
?>