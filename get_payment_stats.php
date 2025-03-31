<?php
require 'config.php';

try {
    $stmt = $conn->query("
        SELECT 
            payment_method,
            COUNT(*) AS total_transactions,
            SUM(amount) AS total_revenue
        FROM payments
        GROUP BY payment_method
    ");
    
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($stats);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve payment statistics']);
}
?>