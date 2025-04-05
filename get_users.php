<?php
require_once 'config.php';

try {
    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Base query
    $query = "SELECT 
                user_id, 
                username, 
                email, 
                role, 
                created_at
              FROM users 
              ORDER BY created_at DESC 
              LIMIT ? OFFSET ?";

    // Prepare and execute query
    $stmt = $connect->prepare($query);
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();

    // Get results
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);

    // Get total count for pagination
    $totalStmt = $connect->query("SELECT COUNT(*) AS total FROM users");
    $totalUsers = $totalStmt->fetch_assoc()['total'];

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'data' => $users,
        'meta' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $totalUsers,
            'total_pages' => ceil($totalUsers / $perPage)
        ]
    ]);

} catch (mysqli_sql_exception $e) {
    error_log("Get users error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve user data']);
}