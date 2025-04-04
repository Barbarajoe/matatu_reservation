<?php
require_once 'config.php';



try {
    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Main query with your specified SQL
    $stmt = $conn->prepare("
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
        LIMIT :limit OFFSET :offset
    ");

    // Bind parameters
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $activeUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $countStmt = $conn->prepare("
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
    $totalUsers = $countStmt->fetchColumn();

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

} catch(PDOException $e) {
    error_log("Active users error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve active users']);
}