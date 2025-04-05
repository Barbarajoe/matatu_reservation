<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Debugging: Check if session is set
if (!isset($_SESSION['user_id'])) {
    echo "Redirecting to login.php...";
    header("Location: login.php");
    exit();
}

try {
    $stmt = $connect->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    
    $user = $stmt->fetch();
    
    if ($user) {
        echo "Welcome, " . htmlspecialchars($user['username']);
    } else {
        echo "User not found. Redirecting to login.php...";
        header("Location: login.php");
        exit();
    }
} catch(PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>