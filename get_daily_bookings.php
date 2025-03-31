<?php
require 'config.php';

try {
    $stmt = $conn->query("
        SELECT 
            DATE(booking_time) AS booking_date,
            COUNT(*) AS total_bookings
        FROM bookings
        GROUP BY DATE(booking_time)
        ORDER BY booking_date DESC
        LIMIT 7
    ");
    
    $dailyBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($dailyBookings);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve booking data']);
}
?>