<?php
require_once 'config.php';

/**
 * Validates the format of an email address.
 *
 * @param string $email The email address to validate.
 * @return bool True if the email is valid, false otherwise.
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validates the format of a phone number.
 *
 * @param string $phone The phone number to validate.
 * @return bool True if the phone number is valid, false otherwise.
 */
function validatePhone($phone) {
    return preg_match('/^2547\d{8}$/', $phone) === 1;
}

/**
 * Verifies the CSRF token to prevent cross-site request forgery.
 *
 * @param string $token The CSRF token to verify.
 * @return bool True if the token is valid, false otherwise.
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Verify administrator privileges
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: login.html");
    exit();
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'])) {
            throw new Exception("CSRF token validation failed");
        }

        // Sanitize inputs
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $password = $_POST['password'];
        $role = sanitizeInput($_POST['role']);

        // Validate inputs
        if (empty($username) || empty($email) || empty($phone) || empty($password)) {
            throw new Exception("All fields are required");
        }

        if (!validateEmail($email)) {
            throw new Exception("Invalid email format");
        }

        if (!validatePhone($phone)) {
            throw new Exception("Phone must be in 2547XXXXXXXX format");
        }

        if (!in_array($role, ['administrator', 'operator', 'passenger'])) {
            throw new Exception("Invalid user role");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }

        // Check for existing username/email
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("Username or email already exists");
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users 
                              (username, email, phone, password, role)
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $phone, $hashedPassword, $role]);

        // Log the action
        auditLog('user_added', "Added new user: $username");

        $success = "User added successfully!";
        $_POST = []; // Clear form fields

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New User</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h2>Add New User</h2>
        
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label>Phone:</label>
                <input type="tel" name="phone" 
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" 
                       pattern="2547[0-9]{8}" 
                       title="2547XXXXXXXX" 
                       required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Role:</label>
                <select name="role" required>
                    <option value="administrator">Administrator</option>
                    <option value="operator">Operator</option>
                    <option value="passenger" selected>Passenger</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Add User</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>