<?php
require_once 'config.php';

// Verify administrator privileges
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

try {
    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Base query
    $query = "SELECT 
                id, 
                username, 
                email, 
                phone, 
                role, 
                created_at, 
                last_login 
              FROM users 
              ORDER BY created_at DESC 
              LIMIT :limit OFFSET :offset";

    // Prepare and execute query
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    // Get results
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $totalStmt = $conn->query("SELECT COUNT(*) FROM users");
    $totalUsers = $totalStmt->fetchColumn();

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

} catch(PDOException $e) {
    error_log("Get users error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve user data']);
}