<?php
require 'config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
  return;
}

if (!isset($_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Session expired. Please log in again.']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
print_r($_SESSION);


    try {
        // Verify admin privileges
        if ($_SESSION['role'] !== 'administrator') {
            http_response_code(403);
            die(json_encode(['error' => 'Unauthorized access']));
        }

        $conn->beginTransaction();

        // Delete dependent records
        $conn->exec("DELETE FROM bookings WHERE user_id = $userId");
        $conn->exec("DELETE FROM routes WHERE created_by = $userId");
        
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        $conn->commit();
        echo json_encode(['success' => 'User deleted successfully']);
    } catch(PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete user']);
    }
?>