<?php
require_once 'config.php';

try {
    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Main query with your specified SQL
    $stmt = $connect->prepare("
        SELECT 
            users.user_id, 
            username, 
            email, 
            MAX(updated_at) AS last_login
        FROM users
        LEFT JOIN audit_logs ON users.user_id = audit_logs.user_id
        WHERE action_type = 'login'
        GROUP BY users.user_id
        HAVING last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY last_login DESC
        LIMIT ? OFFSET ?
    ");

    // Bind parameters
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $stmt->bind_result($user_id, $username, $email, $last_login);
    $activeUsers = [];
    while ($stmt->fetch()) {
        $activeUsers[] = [
            'user_id' => $user_id,
            'username' => $username,
            'email' => $email,
            'last_login' => $last_login
        ];
    }

    // Get total count
    $countStmt = $connect->prepare("
        SELECT COUNT(*) FROM (
            SELECT users.user_id
            FROM users
            LEFT JOIN audit_logs ON users.user_id = audit_logs.user_id
            WHERE action_type = 'login'
            GROUP BY users.user_id
            HAVING MAX(updated_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ) AS active_users
    ");
    $countStmt->execute();
    $countStmt->bind_result($totalUsers);
    $countStmt->fetch();

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'data' => $activeUsers,
        'meta' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $totalUsers,
            'total_pages' => ceil($totalUsers / $perPage)
        ]
    ]);

} catch (mysqli_sql_exception $e) {
    error_log("Active users error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve active users']);
}