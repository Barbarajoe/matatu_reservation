<?php
header('Content-Type: application/json');
require 'config.php';

try {
    $stmt = $conn->query("
        SELECT r.*, v.registration 
        FROM routes r
        LEFT JOIN vehicles v ON r.vehicle_id = v.vehicle_id
    ");
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($routes);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>