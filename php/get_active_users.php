<?php
require 'api/config.php';

try {
    $stmt = $conn->prepare("
        SELECT users.id, username, email, MAX(login_time) AS last_login
        FROM users
        LEFT JOIN audit_logs ON users.id = audit_logs.user_id
        WHERE action = 'login'
        GROUP BY users.id
        HAVING last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY last_login DESC
    ");
    
    $stmt->execute();
    $activeUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($activeUsers);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch active users']);
}
?>