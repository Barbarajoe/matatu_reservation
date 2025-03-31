<?php
require 'config.php';

try {
    $stmt = $conn->query("
        SELECT id, name, price 
        FROM routes 
        ORDER BY name ASC
    ");
    
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($routes);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch routes']);
}
?>