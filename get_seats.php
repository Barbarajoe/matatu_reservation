<?php
require 'config.php';
header('Content-Type: application/json');

try {
    // Validate inputs
    $route_id = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
    $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
    
    if (!$route_id || !$date) {
        throw new Exception("Invalid request parameters");
    }

    // Get vehicle capacity
    $stmt = $conn->prepare("
        SELECT v.seat_capacity 
        FROM routes r
        JOIN vehicles v ON r.vehicle_id = v.vehicle_id
        WHERE r.vehicle_id = ?
    ");
    $stmt->execute([$route_id]);
    $capacity = $stmt->fetchColumn();

    if (!$capacity) {
        throw new Exception("Vehicle information not found");
    }

    // Get booked seats
    $stmt = $conn->prepare("
        SELECT seat_numbers 
        FROM bookings 
        WHERE route_id = ? 
        AND travel_date = ?
        AND status IN ('confirmed', 'pending')
    ");
    $stmt->execute([$route_id, $date]);
    $booked_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Generate all possible seats
    $all_seats = [];
    for ($row = 1; $row <= ceil($capacity / 2); $row++) {
        $all_seats[] = $row . 'A';
        $all_seats[] = $row . 'B';
    }

    // Process booked seats
    $taken_seats = [];
    foreach ($booked_seats as $seats) {
        $taken_seats = array_merge($taken_seats, explode(',', $seats));
    }

    // Determine available seats
    $available_seats = array_diff($all_seats, $taken_seats);

    echo json_encode([
        'status' => 'success',
        'available_seats' => array_values($available_seats),
        'seat_map' => $all_seats
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>