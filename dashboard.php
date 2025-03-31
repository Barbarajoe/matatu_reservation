<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    $user = $stmt->fetch();
    
    echo "Welcome, " . $user['username'];
    
} catch(PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>