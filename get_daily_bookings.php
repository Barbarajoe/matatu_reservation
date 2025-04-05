<?php
require 'config.php';

try {
    $stmt = $connect->query("
        SELECT 
            DATE(booking_date) AS booking_date,
            COUNT(*) AS total_bookings
        FROM bookings
        GROUP BY DATE(booking_date)
        ORDER BY booking_date DESC
        LIMIT 7
    ");
    
    $dailyBookings = [];
    while ($row = $stmt->fetch_assoc()) {
        $dailyBookings[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($dailyBookings);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve booking data']);
}
?>